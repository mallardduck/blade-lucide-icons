<?php

declare(strict_types=1);

namespace MallardDuck\LucideIcons;

use BladeUI\Icons\Factory;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;

final class BladeLucideIconsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerConfig();

        $this->callAfterResolving(Factory::class, function (Factory $factory, Container $container) {
            $config = $container->make('config')->get('blade-lucide-icons', []);

            $factory->add('lucide', array_merge(['path' => __DIR__.'/../resources/svg'], $config));
        });
    }

    private function registerConfig(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/blade-lucide-icons.php', 'blade-lucide-icons');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/svg' => public_path('vendor/blade-lucide-icons'),
            ], 'blade-lucide-icons');

            $this->publishes([
                __DIR__.'/../config/blade-lucide-icons.php' => $this->app->configPath('blade-lucide-icons.php'),
            ], 'blade-lucide-icons-config');
        }
    }
}
