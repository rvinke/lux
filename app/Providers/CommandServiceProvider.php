<?php namespace App\Providers;

use App\Console\Commands\CheckLight;
use Illuminate\Support\ServiceProvider;
use App\Console\Commands\MyCommand;

class CommandServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('command.checklight', function()
        {
            return new CheckLight;
        });

        $this->commands(
            'command.checklight'
        );
    }
}