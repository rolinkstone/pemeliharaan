<?php

namespace App\Filament\Resources\PerawatanKendaraanResource\Pages;

use App\Filament\Resources\PerawatanKendaraanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPerawatanKendaraans extends ListRecords
{
    protected static string $resource = PerawatanKendaraanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
