<?php

namespace pxlrbt\FilamentSpotlight;

use Filament\Events\ServingFilament;
use Filament\Facades\Filament;
use Filament\PluginServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use pxlrbt\FilamentSpotlight\Actions\RegisterPages;
use pxlrbt\FilamentSpotlight\Actions\RegisterResources;
use pxlrbt\FilamentSpotlight\Actions\RegisterUserMenu;
use Spatie\LaravelPackageTools\Package;

class FilamentSpotlightServiceProvider extends PluginServiceProvider
{
    public static string $name = 'filament-spotlight';

    protected array $styles = [
        'spotlight' => __DIR__.'/../resources/dist/css/spotlight.css',
    ];

    protected array $beforeCoreScripts = [
        'spotlight' => __DIR__.'/../resources/dist/js/spotlight.js',
    ];

    public function packageConfiguring(Package $package): void
    {
        Config::set('livewire-ui-spotlight.include_js', false);
        Config::set('livewire-ui-spotlight.commands', []);

        Event::listen(ServingFilament::class, [$this, 'registerSpotlight']);
    }

    public function registerSpotlight(ServingFilament $event): void
    {
        if (! Filament::auth()->check()) {
            return;
        }

        (new RegisterPages())();
        (new RegisterResources())();
        (new RegisterUserMenu())();

        // Getting the title from pages needs to instantiate the Livewire components which will disable cache
        Livewire::enableBackButtonCache();

        Filament::registerRenderHook('scripts.end', fn () => Blade::render("@livewire('livewire-ui-spotlight')"));
    }

    


    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/cswni-spotlight.php', 'cswni-spotlight');
        $this->configurePublishing();
    }

    protected function configurePublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/cswni-spotlight.php' => config_path('cswni-spotlight.php'),
        ], 'cswni-spotlight-config');
    }
}
