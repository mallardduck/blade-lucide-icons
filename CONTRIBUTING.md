# Contributing to Blade Lucide Icons

Thank you for considering contributing to Blade Lucide Icons! This document provides guidelines and instructions for local development.

## Prerequisites

- PHP 7.4 or higher
- Composer
- Git
- Make (optional but recommended)

## Development Setup

This project uses a git submodule to track the upstream [Lucide Icons](https://github.com/lucide-icons/lucide) repository. When contributing or working locally, you need to ensure the submodule stays in sync.

### Initial Setup

For first-time setup, run:

```bash
make setup
```

This will:
- Install git hooks for automatic submodule syncing
- Initialize and sync the lucide submodule

**Note for Windows users:** If `make` is not available, you can:
- Install via [Chocolatey](https://chocolatey.org/): `choco install make`
- Use [Git Bash](https://git-scm.com/downloads) which includes make
- Use WSL (Windows Subsystem for Linux)
- Manually run the commands listed in the Makefile

### Available Commands

```bash
make help          # Show all available commands
make sync          # Manually sync submodules
make update        # Pull latest changes and sync submodules
make install-hooks # Install git hooks
make test          # Run test suite
```

### Git Hooks

After running `make setup` or `make install-hooks`, git hooks will automatically sync the submodule when you:
- Pull changes (`git pull`)
- Switch branches (`git checkout`)

If you prefer manual control, you can skip installing hooks and use `make sync` or `make update` instead.

### Manual Submodule Management

If you need to manually manage submodules:

```bash
# Initialize and update all submodules
git submodule update --init --recursive

# Update submodules to latest commit referenced by main repo
git submodule update --recursive
```

## Running Tests

```bash
make test
# or
php vendor/bin/phpunit
```

## Code Style

Please follow the existing code style in the project. Run tests before submitting pull requests to ensure everything works correctly.

## Submitting Changes

1. Fork the repository
2. Create a new branch for your feature or bugfix
3. Run `make setup` to configure your local environment
4. Make your changes
5. Run `make test` to ensure tests pass
6. Commit your changes with clear, descriptive messages
7. Push to your fork and submit a pull request

## Questions?

If you have questions about contributing, feel free to open an issue for discussion.