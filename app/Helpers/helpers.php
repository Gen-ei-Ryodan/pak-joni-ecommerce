<?php

if (! function_exists('image_url')) {
    /**
     * Resolve an image path to a full URL.
     * Handles both external URLs (https://) and relative storage paths.
     */
    function image_url(?string $path): string
    {
        if (empty($path)) {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/' . $path);
    }
}
