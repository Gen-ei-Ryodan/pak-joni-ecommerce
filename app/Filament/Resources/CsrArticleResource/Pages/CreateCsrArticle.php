<?php

namespace App\Filament\Resources\CsrArticleResource\Pages;

use App\Filament\Resources\CsrArticleResource;
use Filament\Resources\Pages\CreateRecord;


use App\Filament\Traits\RedirectsToList;class CreateCsrArticle extends CreateRecord
{
    protected static string $resource = CsrArticleResource::class;

    use RedirectsToList;
}
