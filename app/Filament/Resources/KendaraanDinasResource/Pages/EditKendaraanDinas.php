<?php

namespace App\Filament\Resources\KendaraanDinasResource\Pages;

use App\Filament\Resources\KendaraanDinasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKendaraanDinas extends EditRecord
{
    protected static string $resource = KendaraanDinasResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
