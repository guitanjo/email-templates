<?php

namespace Visualbuilder\EmailTemplates;

use Filament\PluginServiceProvider;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelPackageTools\Package;
use Visualbuilder\EmailTemplates\Helpers\TokenHelper;
use Visualbuilder\EmailTemplates\Commands\InstallCommand;
use Visualbuilder\EmailTemplates\Contracts\TokenHelperInterface;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource;
use Visualbuilder\EmailTemplates\Http\Controllers\EmailTemplateController;

class EmailTemplatesServiceProvider extends PluginServiceProvider
{
    protected array $resources = [
        EmailTemplateResource::class,
    ];

    protected array $styles = [
        'vb-email-templates-styles' => 'https://cdn.jsdelivr.net/gh/lipis/flag-icons@6.6.6/css/flag-icons.min.css',
    ];

    public function configurePackage(Package $package): void
    {
        $package->name("filament-email-templates")
            ->hasMigrations(['create_email_templates_table'])
            ->hasConfigFile(['email-templates', 'filament-tiptap-editor'])
            ->hasAssets()
            ->hasViews('vb-email-templates')
            ->runsMigrations()
            ->hasCommand(InstallCommand::class);
    }

    public function register()
    {
        parent::register();
        $this->app->singleton(TokenHelperInterface::class, TokenHelper::class);
        $this->app->register(EmailTemplatesEventServiceProvider::class);
    }

    public function boot()
    {
        parent::boot();
        if($this->app->runningInConsole()) {
            $this->publishResources();
        }

        $this->registerRoutes();

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'vb-email-templates');
    }

    protected function publishResources()
    {
        $this->publishes([
                             __DIR__
                             .'/../database/seeders/EmailTemplateSeeder.php' => database_path('seeders/EmailTemplateSeeder.php'),
                         ], 'filament-email-templates-seeds');

        $this->publishes([
                             __DIR__.'/../media/' => public_path('media/email-templates'),
                         ], 'filament-email-templates-assets');

        $this->publishes([
                             __DIR__.'/../resources/views' => resource_path('views/vendor/vb-email-templates'),
                         ], 'filament-email-templates-assets');
    }

    /**
     * Register custom routes.
     * We may want to move these to a separate file.
     * @return void
     */
    public function registerRoutes()
    {
        Route::get('/admin/email-templates/{record}/preview', [EmailTemplateController::class, 'preview'])->name('email-template.preview');
        Route::get('/admin/email-templates/{record}/generate-mailable', [EmailTemplateController::class, 'generateMailable'])->name('email-template.generateMailable');
    }
}
