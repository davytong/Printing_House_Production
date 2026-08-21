<?php

if (!function_exists('t')) {
    /**
     * Get translation in current language only
     */
    function t(string $key, string $file = 'common'): string
    {
        return __("{$file}.{$key}");
    }
}

if (!function_exists('trans_only')) {
    /**
     * Get translation in current language only
     */
    function trans_only(string $key, string $file = 'common'): string
    {
        return __("{$file}.{$key}");
    }
}
