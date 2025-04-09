<?php

namespace App\Filament\Resources\BarangPersediaanResource\Pages;

use App\Filament\Resources\BarangPersediaanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBarangPersediaan extends EditRecord
{
    protected static string $resource = BarangPersediaanResource::class;
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
