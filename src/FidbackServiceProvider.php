<?php

namespace Interpro\Fidback;

use Illuminate\Support\ServiceProvider;
use Illuminate\Bus\Dispatcher;

class FidbackServiceProvider extends ServiceProvider {

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(Dispatcher $dispatcher)
    {
        //Publishes package config file to applications config folder
        $this->publishes([__DIR__.'/config/fidback.php' => config_path('fidback.php')]);

        $dispatcher->maps([
            'Interpro\Fidback\Concept\Command\RegisterMessageCommand' => 'Interpro\Fidback\Laravel\Handle\RegisterMessageCommandHandler@handle',

            'Interpro\Fidback\Concept\Command\Image\RefreshAllGroupImageCommand' => 'Interpro\Fidback\Laravel\Handle\Image\RefreshAllGroupImageCommandHandler@handle'
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            'Interpro\Fidback\Concept\Desk',
            'Interpro\Fidback\Laravel\Desk'
        );

        $this->app->make('Interpro\Fidback\Laravel\Http\FidbackController');

        include __DIR__ . '/Laravel/Http/routes.php';

    }

}

