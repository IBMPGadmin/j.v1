<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Helpers\LegalReferenceHelper;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Make LegalReferenceHelper available as a Blade directive
        Blade::directive('processContent', function ($expression) {
            return "<?php echo App\Helpers\LegalReferenceHelper::processContent($expression); ?>";
        });
        
        // Use bootstrap-4 for pagination
        \Illuminate\Pagination\Paginator::useBootstrap();
    }
}
