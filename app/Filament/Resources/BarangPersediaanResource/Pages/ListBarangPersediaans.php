<?php

namespace App\Filament\Resources\BarangPersediaanResource\Pages;

use App\Filament\Resources\BarangPersediaanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBarangPersediaans extends ListRecords
{
    protected static string $resource = BarangPersediaanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
