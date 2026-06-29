<?php

namespace App\Filament\Resources\DealerResource\Pages;

use App\Filament\Resources\DealerResource;
use App\Filament\Traits\RedirectsToList;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDealer extends EditRecord
{
    use RedirectsToList;

    protected static string $resource = DealerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
