<?php

namespace Plugins\Gallery;

use App\Support\Plugins\PluginBoot;
use Illuminate\Support\ServiceProvider;

class GalleryServiceProvider extends ServiceProvider
{
    /** 启动图集插件。 */
    public function boot(): void
    {
        $file = __DIR__.'/database/migrations/2026_09_19_110000_create_plugin_gallery.php';
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        PluginBoot::migrateFile($file);
        PluginBoot::migrateFile(__DIR__.'/database/migrations/2026_09_19_111000_expand_plugin_gallery_meta.php');
        PluginBoot::migrateFile(__DIR__.'/database/migrations/2026_09_19_112000_expand_plugin_gallery_tags_authors_comments.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'gallery');
        if (! $this->app->routesAreCached()) $this->loadRoutesFrom(__DIR__.'/routes.php');
        PluginBoot::cmsLoops([
            'gallery' => \Plugins\Gallery\Tags\GalleryTag::class,
            'galleryType' => \Plugins\Gallery\Tags\GalleryTypeTag::class,
        ]);
    }
}
