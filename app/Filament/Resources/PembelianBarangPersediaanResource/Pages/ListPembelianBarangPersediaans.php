<?php

namespace App\Filament\Resources\PembelianBarangPersediaanResource\Pages;

use App\Filament\Resources\PembelianBarangPersediaanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPembelianBarangPersediaans extends ListRecords
{
    protected static string $resource = PembelianBarangPersediaanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
