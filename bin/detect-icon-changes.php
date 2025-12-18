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
 * Outputs JSON with change summary and recommended version bump.
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

// Find the previous commit that modified resources/svg/
$result = runGitCommand('git log -2 --format=%H -- resources/svg/');
$commits = $result['output'];

if (count($commits) < 2) {
    // No previous commit to compare against, treat all files as added
    $result = runGitCommand('git ls-files resources/svg/*.svg');
    $added = array_filter(
        $result['output'],
        fn($file) => str_ends_with($file, '.svg')
    );
    $removed = [];
    $modified = [];
} else {
    // Compare HEAD with the previous commit
    $previousCommit = trim($commits[1]);
    $result = runGitCommand("git diff --name-status {$previousCommit} HEAD -- resources/svg/");

    $added = [];
    $removed = [];
    $modified = [];

    foreach ($result['output'] as $line) {
        if (empty(trim($line))) {
            continue;
        }

        $parts = preg_split('/\s+/', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }

        [$status, $file] = $parts;

        // Only process SVG files
        if (!str_ends_with($file, '.svg')) {
            continue;
        }

        switch ($status[0]) {
            case 'A':
                $added[] = $file;
                break;
            case 'D':
                $removed[] = $file;
                break;
            case 'M':
            case 'R':
            case 'C':
                $modified[] = $file;
                break;
        }
    }
}

// Determine version bump type based on changes
$bumpType = 'none';
if (!empty($removed)) {
    $bumpType = 'minor'; // Breaking change: icons removed
} elseif (!empty($added) || !empty($modified)) {
    $bumpType = 'patch'; // Non-breaking: icons added or modified
}

// Extract icon names for better readability (remove path and extension)
$extractName = fn($path) => basename($path, '.svg');

$result = [
    'bump_type' => $bumpType,
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

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
exit(0);