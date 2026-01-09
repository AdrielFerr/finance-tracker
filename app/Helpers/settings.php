<?php

// ============================================
// HELPER - app/Helpers/settings.php
// ============================================

use App\Models\SystemSetting;

if (!function_exists('setting')) {
    /**
     * Get system setting value.
     */
    function setting(string $key, $default = null)
    {
        return SystemSetting::get($key, $default);
    }
}