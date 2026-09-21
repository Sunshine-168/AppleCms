<?php

namespace App\Providers;

use App\Cms\Blade\CmsDirectiveRegistrar;
use App\Cms\CmsViewContext;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class RegisterCmsTagsProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CmsViewContext::class);
    }

    public function boot(CmsDirectiveRegistrar $directives): void
    {
        Blade::directive('conf', function ($expression) {
            return "<?php echo e(config('settings.' . {$expression})); ?>";
        });

        $directives->register();
    }
}
