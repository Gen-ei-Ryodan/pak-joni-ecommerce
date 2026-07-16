<?php

namespace App\Filament\Resources\PromoBannerResource\Pages;

use App\Filament\Resources\PromoBannerResource;
use App\Filament\Traits\RedirectsToList;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditPromoBanner extends EditRecord
{
    use RedirectsToList;

    protected static string $resource = PromoBannerResource::class;

    protected ?string $oldImagePath = null;

    protected function beforeSave(): void
    {
        $this->oldImagePath = $this->record->getOriginal('image_path');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['type'] = 'promo';

        if ((empty($data['image_path']) || !$data['image_path']) && $this->oldImagePath) {
            $data['image_path'] = $this->oldImagePath;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $newImagePath = $this->record->image_path;

        if ($this->oldImagePath && $this->oldImagePath !== $newImagePath) {
            $disk = Storage::disk('public');

            if ($disk->exists($this->oldImagePath)) {
                $disk->delete($this->oldImagePath);
            }

            $directory = dirname($this->oldImagePath);
            if ($directory !== '.' && empty($disk->allFiles($directory))) {
                $disk->deleteDirectory($directory);
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
