<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ImageService
{
    public function storeAsWebp(UploadedFile $file, string $directory): string
    {
        $directory = trim($directory, '/');
        $name = Str::uuid()->toString();

        if ($this->canConvertToWebp($file)) {
            $path = $directory.'/'.$name.'.webp';
            $fullPath = storage_path('app/public/'.$path);

            if (! is_dir(dirname($fullPath))) {
                mkdir(dirname($fullPath), 0755, true);
            }

            $this->convertToWebp($file->getPathname(), $fullPath);

            return 'storage/'.$path;
        }

        $stored = $file->storeAs('public/'.$directory, $name.'.'.$file->getClientOriginalExtension());

        return Str::replaceStart('public/', 'storage/', $stored);
    }

    private function canConvertToWebp(UploadedFile $file): bool
    {
        if (! function_exists('imagewebp')) {
            return false;
        }

        $mime = $file->getMimeType();

        return in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true);
    }

    private function convertToWebp(string $sourcePath, string $targetPath): void
    {
        $info = @getimagesize($sourcePath);

        if (! $info) {
            copy($sourcePath, $targetPath);
            return;
        }

        $mime = $info['mime'] ?? null;

        if ($mime === 'image/webp') {
            copy($sourcePath, $targetPath);
            return;
        }

        if ($mime === 'image/jpeg') {
            $image = imagecreatefromjpeg($sourcePath);
        } elseif ($mime === 'image/png') {
            $image = imagecreatefrompng($sourcePath);
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
        } else {
            copy($sourcePath, $targetPath);
            return;
        }

        imagewebp($image, $targetPath, 82);
        imagedestroy($image);
    }
}
