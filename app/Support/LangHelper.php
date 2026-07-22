<?php
namespace App\Support;

use Illuminate\Support\Facades\File;

class LangHelper
{
    /**
     * Map of locale short codes to full display names.
     */
    protected static array $localeNames = [
        'en' => 'United Kingdom',
        'pt' => 'Portugal',
        // 'es' => 'Spain (Spanish)',
        // 'fr' => 'France (French)',
    ];

    /**
     * Returns available locales from JSON lang files, e.g. ['en', 'pt'].
     */
    public static function availableLocales(): array
    {
        $path = base_path('lang');
        if (!File::isDirectory($path)) {
            return [config('app.locale')];
        }

        $locales = [];
        foreach (File::files($path) as $file) {
            if ($file->getExtension() === 'json') {
                $locales[] = $file->getFilenameWithoutExtension();
            }
        }

        return !empty($locales) ? $locales : [config('app.locale')];
    }

    /**
     * Full display name for a locale code, with its currency symbol appended.
     * e.g. "Portugal (€)". Falls back to just the name when no symbol is set.
     */
    public static function localeName(string $locale): string
    {
        $name = static::$localeNames[$locale] ?? strtoupper($locale);
        $symbol = static::symbol($locale);

        return $symbol !== '' ? "{$name} ({$symbol})" : $name;
    }

    /**
     * Currency symbol for a locale, read from company settings.
     * Returns '' if unavailable, so it never breaks label output.
     */
    public static function symbol(string $locale): string
    {
        // Prefer the cached global helper if it exists (no extra query).
        if (function_exists('currency_symbol')) {
            return (string) currency_symbol($locale);
        }

        // Fallback: read directly from the settings model.
        try {
            $setting = \App\Models\CompanySetting::current();
            $symbols = $setting->currency_symbols ?? [];

            if (is_array($symbols) && !empty($symbols[$locale])) {
                return (string) $symbols[$locale];
            }
        } catch (\Throwable $e) {
            // Swallow — labels should never crash over a missing symbol.
        }

        return '';
    }

    /**
     * Returns ['en' => 'United Kingdom (£)', ...] for available locales only.
     */
    public static function localesWithNames(): array
    {
        $out = [];
        foreach (static::availableLocales() as $loc) {
            $out[$loc] = static::localeName($loc);
        }
        return $out;
    }
}
