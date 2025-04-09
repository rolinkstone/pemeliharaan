<?php

namespace App\Filament\Resources\PermintaanPrasaranaResource\Pages;

use App\Filament\Resources\PermintaanPrasaranaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPermintaanPrasarana extends EditRecord
{
    protected static string $resource = PermintaanPrasaranaResource::class;
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
