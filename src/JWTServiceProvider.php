<?php

namespace AdrianoAlves\Jwt;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Console\AboutCommand;

use AdrianoAlves\Jwt\Commands\MakeJwtKeys;
use AdrianoAlves\Jwt\Commands\JwtInstall;
use AdrianoAlves\Jwt\Exceptions\InvalidUserProviderException;

class JWTServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        AboutCommand::add('JWT package to integrate lcobucci\'s jwt package easily in Laravel/Lumen projects ', fn () => ['Version' => '0.1.0']);
        
        $this->publishFiles();

        $this->setJWTGuard();
    }

    protected function publishFiles()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/config/' . Config::CONFIG_FILE . '.php' => config_path(Config::CONFIG_FILE . '.php'),
            ], 'config');

            $this->commands([
                JwtInstall::class,
                MakeJwtKeys::class,
            ]);
        }
    }

    /**
     * Set up the JWT guard with the driver
     * @throws InvalidUserProviderException
     * @return void
     */
    protected function setJWTGuard(): void
    {
        Auth::extend('jwt', function ($app, $name, array $config) {
            // user provider entity specified in the app config file
            $provider = Auth::createUserProvider($config['provider']);

            if($provider !== null) {
                return new JWTGuard($provider, $app->make('request'));
            }

            throw new InvalidUserProviderException("UserProvider cannot be null");
        });
    }
}
