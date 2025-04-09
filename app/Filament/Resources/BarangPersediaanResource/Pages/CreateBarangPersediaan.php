<?php

namespace App\Filament\Resources\BarangPersediaanResource\Pages;

use App\Filament\Resources\BarangPersediaanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBarangPersediaan extends CreateRecord
{
    protected static string $resource = BarangPersediaanResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
}
