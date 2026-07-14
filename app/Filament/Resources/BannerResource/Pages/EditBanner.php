<?php

namespace App\Filament\Resources\BannerResource\Pages;

use App\Filament\Resources\BannerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Traits\RedirectsToList;
use Illuminate\Support\Facades\Storage;

class EditBanner extends EditRecord
{
    use RedirectsToList;

    protected static string $resource = BannerResource::class;

    protected ?string $oldImagePath = null;

    protected function beforeSave(): void
    {
        $this->oldImagePath = $this->record->getOriginal('image_path');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Set default values untuk kolom yang tidak ada di form
        $data['title'] = 'Banner';
        $data['type'] = 'hero';
        $data['is_active'] = true;
        $data['sort_order'] = 0;

        // Jika tidak ada gambar baru, pertahankan yang lama
        if ((empty($data['image_path']) || !$data['image_path']) && $this->oldImagePath) {
            $data['image_path'] = $this->oldImagePath;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $newImagePath = $this->record->image_path;

        // Hapus file lama jika gambar diganti
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
