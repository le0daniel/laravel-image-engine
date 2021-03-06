<?php
/**
 * Created by PhpStorm.
 * User: leodanielstuder
 * Date: 27.10.19
 * Time: 12:35
 */

namespace le0daniel\Laravel\ImageEngine;

use Illuminate\Support\ServiceProvider;
use Intervention\Image\ImageManager;
use le0daniel\Laravel\ImageEngine\Commands\FilesystemClear;
use le0daniel\Laravel\ImageEngine\Commands\ImageUrl;
use le0daniel\Laravel\ImageEngine\Image\ImageEngine;

class ImageEngineProvider extends ServiceProvider
{

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/image-engine.php',
            'image-engine'
        );

        $this->app->singleton(
            ImageEngine::class,
            static function ($container) {
                return new ImageEngine(
                    $container->make(ImageManager::class),
                    config('image-engine.key'),
                    config('image-engine.tmp_directory')
                );
            }
        );
    }

    public function boot()
    {
        require_once __DIR__ . '/helpers.php';

        /* Publish the config */
        $this->publishes(
            [
                __DIR__ . '/config/image-engine.php' => config_path('image-engine.php'),
            ]
        );

        /* Register Routes */
        $this->loadRoutesFrom(__DIR__ . '/routes/resources.php');

        if ($this->app->runningInConsole()) {
            $this->commands(
                [
                    ImageUrl::class,
                    FilesystemClear::class,
                ]
            );
        }
    }

}
