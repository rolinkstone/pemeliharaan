<?php

namespace App\Filament\Resources\PermintaanDriverResource\Pages;

use App\Filament\Resources\PermintaanDriverResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePermintaanDriver extends CreateRecord
{
    protected static string $resource = PermintaanDriverResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
