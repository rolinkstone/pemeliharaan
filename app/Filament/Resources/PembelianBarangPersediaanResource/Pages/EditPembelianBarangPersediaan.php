<?php

namespace App\Filament\Resources\PembelianBarangPersediaanResource\Pages;

use App\Filament\Resources\PembelianBarangPersediaanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPembelianBarangPersediaan extends EditRecord
{
    protected static string $resource = PembelianBarangPersediaanResource::class;

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
