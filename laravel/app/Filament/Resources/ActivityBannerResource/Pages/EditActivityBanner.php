<?php

namespace App\Filament\Resources\ActivityBannerResource\Pages;

use App\Filament\Resources\ActivityBannerResource;
use App\Filament\Traits\RedirectsToList;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditActivityBanner extends EditRecord
{
    use RedirectsToList;

    protected static string $resource = ActivityBannerResource::class;

    protected ?string $oldImagePath = null;

    protected function beforeSave(): void
    {
        $this->oldImagePath = $this->record->getOriginal('image_path');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['type'] = 'kegiatan';

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
