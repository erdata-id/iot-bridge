<?php 

namespace Erdata\IotBridge;

use Illuminate\Support\ServiceProvider;

class IotBridgeServiceProvider extends ServiceProvider
{
    public function boot() {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../config' => config_path(), 'iotbridge-config']);
        }
    }

    public function register() {
        $this->mergeConfigFrom(__DIR__ . '/../config/iotbridge.php', 'config');
    }
    
}