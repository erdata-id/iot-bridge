<?php 

namespace Erdata\IotBridge;

use Illuminate\Support\ServiceProvider;

class IotBridgeServiceProvider extends ServiceProvider
{
    public function boot() {
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('iotbridge.php'),
        ]);
    }

    public function register() {
        $this->mergeConfigFrom(
            __DIR__.'/../config/config.php', 'iotbridge'
        );

        $this->app->bind(IotBridge::class, function ($app) {
            return new IotBridge(
                $app->make('config')->get('iotbridge')
            );
        });
    }
    
}