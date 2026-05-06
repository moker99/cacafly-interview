<?php

namespace App\Providers;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use Spatie\Dropbox\Client as DropboxClient;
use Spatie\FlysystemDropbox\DropboxAdapter;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Storage::extend('dropbox', function ($app, $config) {
            $client  = new DropboxClient($config['authorization_token']);
            $adapter = new DropboxAdapter($client);

            return new FilesystemAdapter(
                new Filesystem($adapter, ['case_sensitive' => false]),
                $adapter,
                $config
            );
        });
    }
}
