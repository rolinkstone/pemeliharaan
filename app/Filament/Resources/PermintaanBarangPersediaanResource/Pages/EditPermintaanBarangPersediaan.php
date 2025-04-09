<?php

namespace App\Filament\Resources\PermintaanBarangPersediaanResource\Pages;

use App\Filament\Resources\PermintaanBarangPersediaanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPermintaanBarangPersediaan extends EditRecord
{
    protected static string $resource = PermintaanBarangPersediaanResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            //
            
            
        ];
    }
}
