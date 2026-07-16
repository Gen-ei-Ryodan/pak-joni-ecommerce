<?php

namespace App\Filament\Resources\HeroVideoResource\Pages;

use App\Filament\Resources\HeroVideoResource;
use App\Filament\Traits\RedirectsToList;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditHeroVideo extends EditRecord
{
    use RedirectsToList;

    protected static string $resource = HeroVideoResource::class;

    protected ?string $oldVideoPath = null;

    protected function beforeSave(): void
    {
        $this->oldVideoPath = $this->record->getOriginal('video_path');
    }

    protected function afterSave(): void
    {
        $newVideoPath = $this->record->video_path;

        // Hapus file lama jika video diganti
        if ($this->oldVideoPath && $this->oldVideoPath !== $newVideoPath) {
            $disk = Storage::disk('public');

            if ($disk->exists($this->oldVideoPath)) {
                $disk->delete($this->oldVideoPath);
            }

            $directory = dirname($this->oldVideoPath);
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
