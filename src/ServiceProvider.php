<?php

namespace AliasProject\LaravelEchoApiGateway;

use AliasProject\LaravelEchoApiGateway\Commands\VaporHandle;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . "/../config/laravel-echo-api-gateway.php",
            "laravel-echo-api-gateway"
        );

        Config::set("broadcasting.connections.laravel-echo-api-gateway", [
            "driver" => "laravel-echo-api-gateway",
        ]);

        $config = config("laravel-echo-api-gateway");

        $subscriptionRepository = new SubscriptionRepository($config);

        $this->app->bind(
            SubscriptionRepository::class,
            fn() => $subscriptionRepository
        );
        $this->app->bind(
            ConnectionRepository::class,
            fn() => new ConnectionRepository($subscriptionRepository, $config)
        );
    }

    public function boot(BroadcastManager $broadcastManager): void
    {
        $broadcastManager->extend(
            "laravel-echo-api-gateway",
            fn() => $this->app->make(Driver::class)
        );

        $this->commands([VaporHandle::class]);

        $this->publishes(
            [
                __DIR__ .
                "/../config/laravel-echo-api-gateway.php" => config_path(
                    "laravel-echo-api-gateway.php"
                ),
            ],
            "laravel-echo-api-gateway-config"
        );
    }
}
