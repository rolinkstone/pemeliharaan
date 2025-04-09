<?php

namespace App\Filament\Resources\PermintaanBarangPersediaanResource\Pages;

use App\Filament\Resources\PermintaanBarangPersediaanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPermintaanBarangPersediaans extends ListRecords
{
    protected static string $resource = PermintaanBarangPersediaanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
