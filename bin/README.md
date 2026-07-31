# Automation Scripts

This directory contains automation scripts for managing icon updates and releases.

Lucide publishes two independent release trains from the same monorepo: core
icons (plain semver tags, e.g. `1.28.0`) and lab icons (`@lucide/lab@X.Y.Z`
tags). Since a single `lucide` submodule commit contains both `icons/` and
`lab/` at once, these scripts track and report on the two icon sets
independently while still producing a single release of this package.

## Scripts

### `detect-icon-changes.php`

Analyzes changes to icon files between commits and determines the appropriate
semantic version bump, bucketed separately for core icons
(`resources/svg/icons/`) and lab icons (`resources/svg/lab/`).

**Usage:**
```bash
php bin/detect-icon-changes.php
```

**Output:** JSON object containing:
- `bump_type`: Combined recommended version bump (`major`, `minor`, `patch`, or `none`) - the more severe of the two buckets below
- `icons`: `{ bump_type, changes: {added, removed, modified}, summary }` for core icons
- `lab`: `{ bump_type, changes: {added, removed, modified}, summary }` for lab icons

**Logic (per bucket):**
- **None**: No changes detected
- **Patch**: Icons added or modified only
- **Minor**: Any icons removed (breaking change)

This script diffs the actual generated SVG file trees rather than upstream
git tags, so it stays correct even when a submodule bump skips past several
intermediate tags of either release train.

### `bump-version.php`

Bumps the package version and updates the CHANGELOG.md file.

**Usage:**
```bash
php bin/bump-version.php <bump_type> <lucide_version> <lucide_lab_version> [changes_json]
```

**Arguments:**
- `bump_type`: `major`, `minor`, or `patch`
- `lucide_version`: New Lucide core version (e.g., "1.28.0"), or `-` if the core train didn't change this run
- `lucide_lab_version`: New Lucide lab version (e.g., "0.2.0"), or `-` if the lab train didn't change this run
- `changes_json`: Optional JSON string from `detect-icon-changes.php`

**Behavior:**
- Reads current version from git tags
- Calculates new version based on bump type
- Updates CHANGELOG.md with a new entry, with separate bullets for icon vs. lab icon changes, and an `### Updates` line per train that actually changed
- Outputs new version number

## GitHub Actions Integration

These scripts are used by `.github/workflows/update-lucide.yml` to automate:
1. Icon change detection (per iconset)
2. Version bumping
3. CHANGELOG updates
4. Release creation

The workflow runs daily and triggers automatically when new Lucide core or
lab versions are available. It checks out the submodule to whichever of the
two latest tags (core, lab) is the descendant of the other, since that
commit's tree is a superset of both, then re-resolves the actual nearest tag
per style from that commit before reporting versions.
