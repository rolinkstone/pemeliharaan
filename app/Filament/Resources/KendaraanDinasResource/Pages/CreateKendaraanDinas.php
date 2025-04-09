<?php

namespace App\Filament\Resources\KendaraanDinasResource\Pages;

use App\Filament\Resources\KendaraanDinasResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateKendaraanDinas extends CreateRecord
{
    protected static string $resource = KendaraanDinasResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
