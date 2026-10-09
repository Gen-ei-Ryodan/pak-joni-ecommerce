<?php

namespace App\Filament\Resources\VoucherResource\Pages;

use App\Filament\Resources\VoucherResource;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Resources\Pages\CreateRecord;

class CreateVoucher extends CreateRecord
{
    protected static string $resource = VoucherResource::class;

    use InteractsWithForms;

    protected function getFormSchema(): array
    {
        return VoucherResource::form($this->form)->schema();
    }
}