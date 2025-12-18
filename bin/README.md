# Automation Scripts

This directory contains automation scripts for managing icon updates and releases.

## Scripts

### `detect-icon-changes.php`

Analyzes changes to icon files between commits and determines the appropriate semantic version bump.

**Usage:**
```bash
php bin/detect-icon-changes.php
```

**Output:** JSON object containing:
- `bump_type`: Recommended version bump (`major`, `minor`, `patch`, or `none`)
- `changes`: Arrays of added, removed, and modified icon names
- `summary`: Counts of each change type

**Logic:**
- **None**: No changes detected
- **Patch**: Icons added or modified only
- **Minor**: Any icons removed (breaking change)

### `bump-version.php`

Bumps the package version and updates the CHANGELOG.md file.

**Usage:**
```bash
php bin/bump-version.php <bump_type> <lucide_version> [changes_json]
```

**Arguments:**
- `bump_type`: `major`, `minor`, or `patch`
- `lucide_version`: New Lucide version number (e.g., "0.561.0")
- `changes_json`: Optional JSON string from `detect-icon-changes.php`

**Behavior:**
- Reads current version from git tags
- Calculates new version based on bump type
- Updates CHANGELOG.md with new entry
- Outputs new version number

## GitHub Actions Integration

These scripts are used by `.github/workflows/update-lucide.yml` to automate:
1. Icon change detection
2. Version bumping
3. CHANGELOG updates
4. Release creation

The workflow runs daily and triggers automatically when new Lucide versions are available.