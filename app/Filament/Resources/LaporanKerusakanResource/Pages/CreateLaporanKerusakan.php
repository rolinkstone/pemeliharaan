<?php

namespace App\Filament\Resources\LaporanKerusakanResource\Pages;

use App\Filament\Resources\LaporanKerusakanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLaporanKerusakan extends CreateRecord
{
    protected static string $resource = LaporanKerusakanResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
