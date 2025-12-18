#!/usr/bin/env php
<?php

/**
 * Bump version and update CHANGELOG.
 *
 * Usage: php bump-version.php <bump_type> <lucide_version> [changes_json]
 *
 * Arguments:
 *   bump_type: 'major', 'minor', or 'patch'
 *   lucide_version: The new Lucide version (e.g., "0.561.0")
 *   changes_json: Optional JSON string with icon changes
 */

if ($argc < 3) {
    fwrite(STDERR, "Usage: {$argv[0]} <bump_type> <lucide_version> [changes_json]\n");
    exit(1);
}

$bumpType = $argv[1];
$lucideVersion = $argv[2];
$changesJson = $argv[3] ?? null;

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

// Build changelog entry
$entry = "## {$newVersion} - ({$today})\n";

if ($changes && isset($changes['changes'])) {
    $iconChanges = $changes['changes'];

    if (!empty($iconChanges['removed'])) {
        $entry .= "### Removed\n";
        $entry .= "- Removed " . count($iconChanges['removed']) . " icon(s): "
            . implode(', ', array_map(fn($i) => "`{$i}`", array_slice($iconChanges['removed'], 0, 10)))
            . (count($iconChanges['removed']) > 10 ? ', ...' : '') . "\n\n";
    }

    if (!empty($iconChanges['added'])) {
        $entry .= "### Added\n";
        $entry .= "- Added " . count($iconChanges['added']) . " new icon(s)"
            . (count($iconChanges['added']) <= 5 ? ": " . implode(', ', array_map(fn($i) => "`{$i}`", $iconChanges['added'])) : '') . "\n\n";
    }

    if (!empty($iconChanges['modified'])) {
        $entry .= "### Changed\n";
        $entry .= "- Modified " . count($iconChanges['modified']) . " icon(s)\n\n";
    }
}

$entry .= "### Updates\n";
$entry .= "- Update Lucide to `v{$lucideVersion}`\n";

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