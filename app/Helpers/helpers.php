<?php

use App\Models\CompanySetting;
use Illuminate\Support\Facades\Cache;

if (!function_exists('company_setting')) {
    /**
     * Get the company setting instance (cached).
     */
    function company_setting()
    {
        return Cache::rememberForever('company_setting', function () {
            return CompanySetting::current();
        });
    }
}

if (!function_exists('company_logo')) {
    /**
     * Get the company logo URL, or null if not set.
     */
    function company_logo()
    {
        $setting = company_setting();

        return $setting->logo ? asset('public/storage/company/' . $setting->logo) : null;
    }
}

if (!function_exists('company_favicon')) {
    /**
     * Get the company favicon URL, or null if not set.
     */
    function company_favicon()
    {
        $setting = company_setting();

        return $setting->favicon ? asset('public/storage/company/' . $setting->favicon) : null;
    }
}

if (!function_exists('login_cover')) {
    /**
     * Get the login cover URL, or null if not set.
     */
    function login_cover()
    {
        $setting = company_setting();

        return $setting->login_cover ? asset('public/storage/company/' . $setting->login_cover) : null;
    }
}

if (!function_exists('company_name')) {
    /**
     * Get the company name with a fallback.
     */
    function company_name($default = 'Vuexy')
    {
        return company_setting()->name ?: $default;
    }
}

if (!function_exists('currency_symbol')) {
    /**
     * Get the currency symbol for a given locale (defaults to the current app locale).
     * Falls back to an empty string if not set.
     *
     * @param  string|null  $locale  Short locale code, e.g. 'en' or 'pt'
     * @param  string  $default
     * @return string
     */
    function currency_symbol($locale = null, $default = '')
    {
        $locale = $locale ?: app()->getLocale();
        $symbols = company_setting()->currency_symbols ?? [];

        if (is_array($symbols) && !empty($symbols[$locale])) {
            return $symbols[$locale];
        }

        return $default;
    }
}
