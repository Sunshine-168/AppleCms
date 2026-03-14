<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

/**
 * 注册模板标签
 */
class RegisterCmsTagsProvider extends ServiceProvider
{
    public function register(): void
    {
        Blade::directive('conf', function ($expression) {
            return "<?php echo config('system.settings.' . $expression); ?>";
        });
    }
}
