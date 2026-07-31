#!/usr/bin/env php
<?php

/**
 * Detect icon changes and determine version bump type.
 *
 * Compares icon files between HEAD and the previous commit to detect:
 * - Added icons (new SVG files)
 * - Removed icons (deleted SVG files)
 * - Modified icons (changed SVG files)
 *
 * Changes are bucketed per iconset (core `resources/svg/icons/` vs lab
 * `resources/svg/lab/`) since Lucide now publishes those as two
 * independent release trains on the same upstream repo.
 *
 * Outputs JSON with per-iconset change summaries and a combined
 * recommended version bump.
 */

function runGitCommand(string $command): array
{
    $descriptorspec = [
        0 => ['pipe', 'r'],  // stdin
        1 => ['pipe', 'w'],  // stdout
        2 => ['pipe', 'w'],  // stderr
    ];

    $process = proc_open($command, $descriptorspec, $pipes);

    if (!is_resource($process)) {
        return ['output' => [], 'exitCode' => 1];
    }

    fclose($pipes[0]);
    $output = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    $exitCode = proc_close($process);

    return [
        'output' => array_filter(explode("\n", trim($output))),
        'exitCode' => $exitCode,
    ];
}

function bucketFor(string $path): ?string
{
    if (str_starts_with($path, 'resources/svg/icons/')) {
        return 'icons';
    }

    if (str_starts_with($path, 'resources/svg/lab/')) {
        return 'lab';
    }

    return null;
}

function bumpTypeFor(array $added, array $removed, array $modified): string
{
    if (!empty($removed)) {
        return 'minor'; // Breaking change: icons removed
    }

    if (!empty($added) || !empty($modified)) {
        return 'patch'; // Non-breaking: icons added or modified
    }

    return 'none';
}

function summarize(array $added, array $removed, array $modified): array
{
    $extractName = fn ($path) => basename($path, '.svg');

    return [
        'bump_type' => bumpTypeFor($added, $removed, $modified),
        'changes' => [
            'added' => array_map($extractName, $added),
            'removed' => array_map($extractName, $removed),
            'modified' => array_map($extractName, $modified),
        ],
        'summary' => [
            'added_count' => count($added),
            'removed_count' => count($removed),
            'modified_count' => count($modified),
            'total_changes' => count($added) + count($removed) + count($modified),
        ],
    ];
}

$buckets = [
    'icons' => ['added' => [], 'removed' => [], 'modified' => []],
    'lab' => ['added' => [], 'removed' => [], 'modified' => []],
];

// Find the previous commit that modified resources/svg/
$result = runGitCommand('git log -2 --format=%H -- resources/svg/');
$commits = $result['output'];

if (count($commits) < 2) {
    // No previous commit to compare against, treat all files as added
    $result = runGitCommand('git ls-files resources/svg/*.svg resources/svg/**/*.svg');
    foreach ($result['output'] as $file) {
        if (!str_ends_with($file, '.svg')) {
            continue;
        }

        $bucket = bucketFor($file);
        if ($bucket !== null) {
            $buckets[$bucket]['added'][] = $file;
        }
    }
} else {
    // Compare HEAD with the previous commit
    $previousCommit = trim($commits[1]);
    $result = runGitCommand("git diff --name-status {$previousCommit} HEAD -- resources/svg/");

    foreach ($result['output'] as $line) {
        if (empty(trim($line))) {
            continue;
        }

        // Rename/copy lines are tab-separated as "R100\told-path\tnew-path"
        // (three fields), while add/delete/modify lines are "A\tpath" (two
        // fields) - always take the last field as the current path.
        $parts = explode("\t", $line);
        if (count($parts) < 2) {
            continue;
        }

        $status = $parts[0];
        $file = $parts[count($parts) - 1];

        // Only process SVG files
        if (!str_ends_with($file, '.svg')) {
            continue;
        }

        $bucket = bucketFor($file);
        if ($bucket === null) {
            continue;
        }

        switch ($status[0]) {
            case 'A':
                $buckets[$bucket]['added'][] = $file;
                break;
            case 'D':
                $buckets[$bucket]['removed'][] = $file;
                break;
            case 'M':
            case 'R':
            case 'C':
                $buckets[$bucket]['modified'][] = $file;
                break;
        }
    }
}

$iconsSummary = summarize($buckets['icons']['added'], $buckets['icons']['removed'], $buckets['icons']['modified']);
$labSummary = summarize($buckets['lab']['added'], $buckets['lab']['removed'], $buckets['lab']['modified']);

$bumpSeverity = ['none' => 0, 'patch' => 1, 'minor' => 2, 'major' => 3];
$combinedBumpType = $bumpSeverity[$iconsSummary['bump_type']] >= $bumpSeverity[$labSummary['bump_type']]
    ? $iconsSummary['bump_type']
    : $labSummary['bump_type'];

$result = [
    'bump_type' => $combinedBumpType,
    'icons' => $iconsSummary,
    'lab' => $labSummary,
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
exit(0);
