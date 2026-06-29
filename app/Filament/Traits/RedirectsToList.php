<?php

namespace App\Filament\Traits;

trait RedirectsToList
{
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
