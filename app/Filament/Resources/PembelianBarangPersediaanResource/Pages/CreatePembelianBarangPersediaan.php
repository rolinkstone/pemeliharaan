<?php

namespace App\Filament\Resources\PembelianBarangPersediaanResource\Pages;

use App\Filament\Resources\PembelianBarangPersediaanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePembelianBarangPersediaan extends CreateRecord
{
    protected static string $resource = PembelianBarangPersediaanResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
