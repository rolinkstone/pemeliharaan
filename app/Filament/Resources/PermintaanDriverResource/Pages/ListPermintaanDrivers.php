<?php

namespace App\Filament\Resources\PermintaanDriverResource\Pages;

use App\Filament\Resources\PermintaanDriverResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPermintaanDrivers extends ListRecords
{
    protected static string $resource = PermintaanDriverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
