<?php

namespace AndPHP\Console;

use Illuminate\Support\ServiceProvider;

/**
 * Created by PhpStorm.
 * User: DaXiong
 * Date: 2021/4/3
 * Time: 1:23 AM
 */
class CommandServiceProvider extends ServiceProvider
{

    protected $commands = [];

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->commands = [
            \AndPHP\Console\Commands\ModelCommand::class,
            \AndPHP\Console\Commands\ControllerCommand::class,
            \AndPHP\Console\Commands\ServiceCommand::class,
            \AndPHP\Console\Commands\DocsCommand::class,
        ];

        foreach ($this->commands as $command) {
            $this->app->singleton($command, function () use($command) {
                return new $command();
            });
        }


        $this->commands($this->commands);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return $this->commands;
    }
}