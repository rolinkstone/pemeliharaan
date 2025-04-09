<?php

namespace App\Filament\Resources\KendaraanDinasResource\Pages;

use App\Filament\Resources\KendaraanDinasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKendaraanDinas extends ListRecords
{
    protected static string $resource = KendaraanDinasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
