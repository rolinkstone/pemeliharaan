<?php

namespace App\Filament\Resources\PermintaanBarangPersediaanResource\Pages;

use App\Filament\Resources\PermintaanBarangPersediaanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePermintaanBarangPersediaan extends CreateRecord
{
    protected static string $resource = PermintaanBarangPersediaanResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
