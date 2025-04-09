<?php

namespace App\Filament\Resources\PermintaanPrasaranaResource\Pages;

use App\Filament\Resources\PermintaanPrasaranaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPermintaanPrasaranas extends ListRecords
{
    protected static string $resource = PermintaanPrasaranaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
