<?php

namespace App\Providers;

use Filament\PluginServiceProvider;
use Filament\Facades\Filament;
use Illuminate\Support\ServiceProvider;
use App\Filament\Resources\UserResource;

class FilamentServiceProvider extends PluginServiceProvider
{
    public function boot()
    {
        parent::boot();

        Filament::registerResources([
            UserResource::class,
        ]);
    }
}