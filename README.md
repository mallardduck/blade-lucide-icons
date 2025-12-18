# Blade Lucide Icons

<a href="https://github.com/mallardduck/blade-lucide-icons/actions?query=workflow%3ATests">
    <img src="https://github.com/mallardduck/blade-lucide-icons/actions/workflows/tests.yml/badge.svg?branch=main" alt="Tests">
</a>
<a href="https://packagist.org/packages/mallardduck/blade-lucide-icons">
    <img src="https://img.shields.io/packagist/v/mallardduck/blade-lucide-icons" alt="Latest Stable Version">
</a>
<a href="https://packagist.org/packages/mallardduck/blade-lucide-icons">
    <img src="https://img.shields.io/packagist/dt/mallardduck/blade-lucide-icons" alt="Total Downloads">
</a>

A package to easily make use of [Lucide](https://github.com/lucide-icons/lucide) Icons in your Laravel Blade views.

For a full list of available icons see [the SVG directory](resources/svg) or preview them at [lucide.dev](https://lucide.dev/).

## A message to Russian 🇷🇺 people

If you currently live in Russia, please read [this message](./ToRussianPeople.md).

## Requirements

- PHP 8.1 or higher
- Laravel 8.0 or higher

## Installation

```bash
composer require mallardduck/blade-lucide-icons
```

## Blade Icons

Blade LucideIcons uses Blade Icons under the hood. Please refer to [the Blade Icons readme](https://github.com/blade-ui-kit/blade-icons) for additional functionality. We also recommend to [enable icon caching](https://github.com/blade-ui-kit/blade-icons#caching) with this library.

## Configuration

Blade LucideIcons also offers the ability to use features from Blade Icons like default classes, default attributes, etc. If you'd like to configure these, publish the `blade-lucide-icons.php` config file:

```bash
php artisan vendor:publish --tag=blade-lucide-icons-config
```

## Usage

Icons can be used as self-closing Blade components which will be compiled to SVG icons:

```blade
<x-lucide-activity />
```

You can also pass classes to your icon components:

```blade
<x-lucide-album class="w-6 h-6 text-gray-500"/>
```

And even use inline styles:

```blade
<x-lucide-anchor style="color: #555"/>
```

The solid icons can be referenced like this:

```blade
<x-lucide-bike />
```

### Raw SVG Icons

If you want to use the raw SVG icons as assets, you can publish them using:

```bash
php artisan vendor:publish --tag=blade-lucide-icons --force
```

Then use them in your views like:

```blade
<img src="{{ asset('vendor/blade-lucide-icons/cloud-moon.svg') }}" width="10" height="10"/>
```

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for development setup and guidelines.

## Changelog

Check out the [CHANGELOG](CHANGELOG.md) in this repository for all the recent changes.

## Maintainers

Blade LucideIcons is developed and maintained by Dan Pock.

## License

Blade LucideIcons is open-sourced software licensed under [the MIT license](LICENSE.md).
