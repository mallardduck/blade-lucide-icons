#!/usr/bin/env php
<?php

/**
 * Bump version and update CHANGELOG.
 *
 * Usage: php bump-version.php <bump_type> <lucide_version> <lucide_lab_version> [changes_json]
 *
 * Arguments:
 *   bump_type: 'major', 'minor', 'patch', or 'none'
 *   lucide_version: The new Lucide core version (e.g., "1.28.0"), or "-" if unchanged
 *   lucide_lab_version: The new Lucide lab version (e.g., "0.2.0"), or "-" if unchanged
 *   changes_json: Optional JSON string with icon changes (from detect-icon-changes.php)
 */

if ($argc < 4) {
    fwrite(STDERR, "Usage: {$argv[0]} <bump_type> <lucide_version> <lucide_lab_version> [changes_json]\n");
    exit(1);
}

$bumpType = $argv[1];
$lucideVersion = $argv[2];
$lucideLabVersion = $argv[3];
$changesJson = $argv[4] ?? null;

if (!in_array($bumpType, ['major', 'minor', 'patch', 'none'])) {
    fwrite(STDERR, "Invalid bump type: {$bumpType}\n");
    exit(1);
}

if ($bumpType === 'none') {
    fwrite(STDERR, "No version bump needed (no changes detected)\n");
    exit(0);
}

// Parse changes if provided
$changes = null;
if ($changesJson) {
    $changes = json_decode($changesJson, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        fwrite(STDERR, "Invalid JSON for changes\n");
        exit(1);
    }
}

// Get current version from git tags
exec("git tag --sort=-v:refname | head -1", $output, $exitCode);
$currentVersion = $exitCode === 0 && !empty($output) ? trim($output[0]) : '0.0.0';

// Parse current version
if (!preg_match('/^v?(\d+)\.(\d+)\.(\d+)/', $currentVersion, $matches)) {
    fwrite(STDERR, "Could not parse current version: {$currentVersion}\n");
    exit(1);
}

$major = (int) $matches[1];
$minor = (int) $matches[2];
$patch = (int) $matches[3];

// Calculate new version
switch ($bumpType) {
    case 'major':
        $major++;
        $minor = 0;
        $patch = 0;
        break;
    case 'minor':
        $minor++;
        $patch = 0;
        break;
    case 'patch':
        $patch++;
        break;
}

$newVersion = "{$major}.{$minor}.{$patch}";
$today = date('Y-m-d');

// Detect current branch for CHANGELOG unreleased link
exec("git rev-parse --abbrev-ref HEAD", $branchOutput);
$currentBranch = !empty($branchOutput) ? trim($branchOutput[0]) : 'main';

// Update CHANGELOG.md
$changelogPath = __DIR__ . '/../CHANGELOG.md';
if (!file_exists($changelogPath)) {
    fwrite(STDERR, "CHANGELOG.md not found\n");
    exit(1);
}

$changelog = file_get_contents($changelogPath);

/**
 * Render the Added/Changed/Removed bullets for a single iconset bucket
 * (e.g. "icon" or "lab icon"), appending to the given section buffers.
 */
function appendIconsetBullets(array &$sections, ?array $bucket, string $label): void
{
    if ($bucket === null || empty($bucket['changes'])) {
        return;
    }

    $iconChanges = $bucket['changes'];

    if (!empty($iconChanges['removed'])) {
        $sections['Removed'][] = "- Removed " . count($iconChanges['removed']) . " {$label}(s): "
            . implode(', ', array_map(fn ($i) => "`{$i}`", array_slice($iconChanges['removed'], 0, 10)))
            . (count($iconChanges['removed']) > 10 ? ', ...' : '');
    }

    if (!empty($iconChanges['added'])) {
        $sections['Added'][] = "- Added " . count($iconChanges['added']) . " new {$label}(s)"
            . (count($iconChanges['added']) <= 5 ? ": " . implode(', ', array_map(fn ($i) => "`{$i}`", $iconChanges['added'])) : '');
    }

    if (!empty($iconChanges['modified'])) {
        $sections['Changed'][] = "- Modified " . count($iconChanges['modified']) . " {$label}(s)";
    }
}

$sections = ['Removed' => [], 'Added' => [], 'Changed' => []];

if ($changes) {
    appendIconsetBullets($sections, $changes['icons'] ?? null, 'icon');
    appendIconsetBullets($sections, $changes['lab'] ?? null, 'lab icon');
}

// Build changelog entry
$entry = "## {$newVersion} - ({$today})\n";

foreach (['Removed', 'Added', 'Changed'] as $heading) {
    if (!empty($sections[$heading])) {
        $entry .= "### {$heading}\n";
        $entry .= implode("\n", $sections[$heading]) . "\n\n";
    }
}

$updates = [];
if ($lucideVersion !== '-') {
    $updates[] = "- Update Lucide icons to `v{$lucideVersion}`";
}
if ($lucideLabVersion !== '-') {
    $updates[] = "- Update Lucide lab icons to `v{$lucideLabVersion}`";
}

if (!empty($updates)) {
    $entry .= "### Updates\n";
    $entry .= implode("\n", $updates) . "\n";
}

// Insert new entry after "## [Unreleased]" section
$unreleasedPattern = '/(## \[Unreleased\].*?\n)\n*/s';
if (preg_match($unreleasedPattern, $changelog, $matches, PREG_OFFSET_CAPTURE)) {
    $insertPosition = $matches[0][1] + strlen($matches[0][0]);
    $newChangelog = substr($changelog, 0, $insertPosition)
        . $entry . "\n\n"
        . substr($changelog, $insertPosition);
} else {
    // Fallback: insert after the header
    $newChangelog = preg_replace(
        '/(# Changelog.*?\n\n)/s',
        "$1{$entry}\n\n",
        $changelog,
        1
    );
}

// Update the [Unreleased] comparison URL
$newChangelog = preg_replace(
    '/\[Unreleased\]\(https:\/\/github\.com\/mallardduck\/blade-lucide-icons\/compare\/[\d\.]+\.\.\.[^\)]+\)/',
    "[Unreleased](https://github.com/mallardduck/blade-lucide-icons/compare/{$newVersion}...{$currentBranch})",
    $newChangelog
);

file_put_contents($changelogPath, $newChangelog);

// Output new version for GitHub Actions
echo $newVersion;
exit(0);
