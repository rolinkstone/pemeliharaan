<?php

namespace App\Filament\Resources\LaporanKerusakanResource\Pages;

use App\Filament\Resources\LaporanKerusakanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLaporanKerusakan extends EditRecord
{
    protected static string $resource = LaporanKerusakanResource::class;
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
