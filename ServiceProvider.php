<?php

namespace YourVendorName\YourPackageName;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class YourPackageServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        $this->registerRoutes();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'codeGeneration');
    }

    /**
     * @return void
     */
    protected function registerRoutes(): void
    {
        Route::middleware('web')
            ->group(__DIR__ . '/../routes/web.php');
    }

    /**
     * @return void
     */
    public function register(): void
    {
        // کامند‌ها را ثبت کنید
        $this->commands([
            \codeGeneration\codeGeneration\Console\Commands\GenerateModuleCommand::class,
        ]);
    }
}
