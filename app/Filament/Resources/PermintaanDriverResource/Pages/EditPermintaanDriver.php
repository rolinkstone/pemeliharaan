<?php

namespace App\Filament\Resources\PermintaanDriverResource\Pages;

use App\Filament\Resources\PermintaanDriverResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPermintaanDriver extends EditRecord
{
    protected static string $resource = PermintaanDriverResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
          //
        ];
    }
}
