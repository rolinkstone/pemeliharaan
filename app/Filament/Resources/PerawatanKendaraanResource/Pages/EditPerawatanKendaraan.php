<?php

namespace App\Filament\Resources\PerawatanKendaraanResource\Pages;

use App\Filament\Resources\PerawatanKendaraanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPerawatanKendaraan extends EditRecord
{
    protected static string $resource = PerawatanKendaraanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
