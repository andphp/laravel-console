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
            'command.andphp_model'  => \AndPHP\Console\Commands\ModelCommand::class
        ];

        foreach ($this->commands as $key => $command) {
            $this->app->singleton($key, function ($command) {
                $newObj = '\\'.$command;
                return new $newObj();
            });
        }


        $this->commands(array_keys($this->commands));
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array_keys($this->commands);
    }
}