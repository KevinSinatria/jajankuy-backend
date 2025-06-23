<?php

namespace App\Providers;

use Google\Client;
use Google\Service\Drive;
// use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use Masbug\Flysystem\GoogleDriveAdapter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
         Storage::extend('google', function ($app, $config) {
            $client = new Client();
            $client->setClientId($config['clientId']);
            $client->setClientSecret($config['clientSecret']);
            $client->refreshToken($config['refreshToken']); // Penting untuk otentikasi ulang token

            $service = new Drive($client); // Inisiasi Google Drive Service

            $adapter = new GoogleDriveAdapter($service, $config['folderId'] ?? null); // Inisiasi adapter

            return new Filesystem($adapter); // Kembalikan instance Filesystem
        });
    }
}
