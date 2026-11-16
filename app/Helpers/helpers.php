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

        // Public directory images (e.g. images/seeder/1.jpeg)
        if (str_starts_with($path, 'images/') || str_starts_with($path, 'css/') || str_starts_with($path, 'js/') || str_starts_with($path, 'fonts/')) {
            return asset($path);
        }

        // Handle paths that already have a storage/ prefix (legacy data from ImageService)
        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        return asset('storage/'.$path);
    }
}

if (! function_exists('terbilang')) {
    /**
     * Convert a number to Indonesian words, e.g. 67500000 -> ENAM PULUH TUJUH JUTA LIMA RATUS RIBU RUPIAH.
     */
    function terbilang(int|float|string $number): string
    {
        $number = (int) abs($number);
        $satuan = ['', 'SATU', 'DUA', 'TIGA', 'EMPAT', 'LIMA', 'ENAM', 'TUJUH', 'DELAPAN', 'SEMBILAN'];

        $words = function (int $n) use (&$words, $satuan): string {
            if ($n < 10) {
                return $satuan[$n];
            }
            if ($n === 10) {
                return 'SEPULUH';
            }
            if ($n === 11) {
                return 'SEBELAS';
            }
            if ($n < 20) {
                return $satuan[$n % 10].' BELAS';
            }
            if ($n < 100) {
                return $satuan[intdiv($n, 10)].' PULUH'.($n % 10 > 0 ? ' '.$satuan[$n % 10] : '');
            }
            if ($n < 200) {
                return 'SERATUS'.($n % 100 > 0 ? ' '.$words($n % 100) : '');
            }
            if ($n < 1000) {
                return $satuan[intdiv($n, 100)].' RATUS'.($n % 100 > 0 ? ' '.$words($n % 100) : '');
            }
            if ($n < 2000) {
                return 'SERIBU'.($n % 1000 > 0 ? ' '.$words($n % 1000) : '');
            }
            if ($n < 1_000_000) {
                return $words(intdiv($n, 1000)).' RIBU'.($n % 1000 > 0 ? ' '.$words($n % 1000) : '');
            }
            if ($n < 1_000_000_000) {
                return $words(intdiv($n, 1_000_000)).' JUTA'.($n % 1_000_000 > 0 ? ' '.$words($n % 1_000_000) : '');
            }

            return $words(intdiv($n, 1_000_000_000)).' MILIAR'.($n % 1_000_000_000 > 0 ? ' '.$words($n % 1_000_000_000) : '');
        };

        return ($number === 0 ? 'NOL' : $words($number)).' RUPIAH';
    }
}
