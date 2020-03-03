<?php

namespace Imageplus\Sns\src;

use Illuminate\Support\ServiceProvider;

class SnsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //only 1 instance of the sns manager is required
        $this->app->singleton('sns_manager', SnsManager::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //copy the default migrations into the migrations folder
        $this->copyMigrations();

        //copy the default config into the config folder
        $this->copyConfig();

        //things that need copying
        //  -> Config -> done
        //  -> Models -> done
        //  -> Migrations -> done
        //  -> Service Provider -> done

        //things to build
        //  -> Error Handler
        //      -> Single Class
        //      -> Switch Statement throwing different exceptions
    }

    protected function copyMigrations(){
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations')
        ], 'migrations');
    }

    protected function copyConfig(){
        $this->publishes([
            __DIR__ . '/../config/sns.php' => config_path('sns.php')
        ], 'config');
    }
}
