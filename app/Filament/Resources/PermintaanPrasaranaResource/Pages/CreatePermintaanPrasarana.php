<?php

namespace App\Filament\Resources\PermintaanPrasaranaResource\Pages;

use App\Filament\Resources\PermintaanPrasaranaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePermintaanPrasarana extends CreateRecord
{
    protected static string $resource = PermintaanPrasaranaResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}
