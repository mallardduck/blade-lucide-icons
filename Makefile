.PHONY: help setup install-hooks sync update test

# Default target shows help
help:
	@echo "Blade Lucide Icons - Development Commands"
	@echo ""
	@echo "Usage: make [target]"
	@echo ""
	@echo "Available targets:"
	@echo "  setup         - Initial setup: install hooks and sync submodules"
	@echo "  install-hooks - Install git hooks for automatic submodule syncing"
	@echo "  sync          - Manually sync submodules to correct version"
	@echo "  update        - Pull latest changes and sync submodules"
	@echo "  test          - Run test suite"
	@echo "  help          - Show this help message"

# Initial setup for new developers
setup: install-hooks sync
	@echo "Setup complete! Submodules will now auto-sync on pull and checkout."

# Install git hooks from .githooks directory
install-hooks:
	@echo "Installing git hooks..."
	@chmod +x .githooks/post-checkout .githooks/post-merge
	@cp .githooks/post-checkout .git/hooks/post-checkout
	@cp .githooks/post-merge .git/hooks/post-merge
	@chmod +x .git/hooks/post-checkout .git/hooks/post-merge
	@echo "Git hooks installed successfully!"
	@echo "Submodules will now auto-sync after pull and checkout operations."

# Manually sync submodules
sync:
	@echo "Syncing submodules..."
	@git submodule update --init --recursive
	@echo "Submodules synced successfully!"

# Pull latest changes and sync submodules
update:
	@echo "Pulling latest changes..."
	@git pull
	@echo "Syncing submodules..."
	@git submodule update --init --recursive
	@echo "Update complete!"

# Run test suite
test:
	@php vendor/bin/phpunit