<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Stichoza\GoogleTranslate\GoogleTranslate;

class LangSync extends Command
{
    /**
     * php artisan lang:sync
     * php artisan lang:sync --base=en          (source language to translate FROM)
     * php artisan lang:sync --no-translate     (just add empty keys, no auto-translate)
     * php artisan lang:sync --paths=app,resources,routes
     */
    protected $signature = 'lang:sync
        {--base=en : The source locale to translate from}
        {--no-translate : Only add missing keys (leave value empty) without calling the translator}
        {--paths= : Comma-separated folders to scan (default: app,resources,routes)}';

    protected $description = 'Scan the codebase for translation keys and sync/translate them across all lang/*.json files';

    public function handle(): int
    {
        $langPath = base_path('lang');

        if (!File::isDirectory($langPath)) {
            File::makeDirectory($langPath, 0755, true);
            $this->warn("Created missing lang directory: {$langPath}");
        }

        // 1. Collect all keys used in code
        $scanPaths = $this->scanPaths();
        $this->info('Scanning: ' . implode(', ', $scanPaths));

        $keys = $this->collectKeys($scanPaths);
        $this->info('Found ' . count($keys) . ' unique translation keys in code.');

        if (empty($keys)) {
            $this->warn('No translation keys found. Nothing to do.');
            return self::SUCCESS;
        }

        // 2. Find all json locale files
        $jsonFiles = collect(File::files($langPath))
            ->filter(fn ($f) => $f->getExtension() === 'json');

        if ($jsonFiles->isEmpty()) {
            $this->error('No lang/*.json files found. Create at least e.g. lang/en.json first.');
            return self::FAILURE;
        }

        $base = $this->option('base');
        $noTranslate = (bool) $this->option('no-translate');

        // 3. Process each locale file
        foreach ($jsonFiles as $file) {
            $locale = $file->getFilenameWithoutExtension();

            $existing = json_decode(File::get($file->getPathname()), true) ?: [];
            $added = 0;

            $translator = null;
            if (!$noTranslate && $locale !== $base) {
                $translator = (new GoogleTranslate())
                    ->setSource($base)
                    ->setTarget($locale);
            }

            foreach ($keys as $key) {
                if (array_key_exists($key, $existing) && $existing[$key] !== '') {
                    continue; // already translated
                }

                if ($locale === $base) {
                    // For the base locale, the key itself is the value
                    $existing[$key] = $key;
                    $added++;
                    continue;
                }

                if ($noTranslate || $translator === null) {
                    $existing[$key] = $existing[$key] ?? '';
                    $added++;
                    continue;
                }

                // Auto-translate
                try {
                    $existing[$key] = $translator->translate($key);
                    $this->line("  [{$locale}] \"{$key}\" → \"{$existing[$key]}\"");
                } catch (\Throwable $e) {
                    $existing[$key] = '';
                    $this->warn("  [{$locale}] failed to translate \"{$key}\": {$e->getMessage()}");
                }
                $added++;
            }

            // 4. Sort keys and write back (pretty + unescaped unicode/slashes)
            ksort($existing);

            File::put(
                $file->getPathname(),
                json_encode(
                    $existing,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                ) . PHP_EOL
            );

            $this->info("[{$locale}] synced — {$added} key(s) added/updated.");
        }

        $this->newLine();
        $this->info('Language sync complete ✔');

        return self::SUCCESS;
    }

    /**
     * Resolve which folders to scan.
     */
    private function scanPaths(): array
    {
        $paths = $this->option('paths');

        $folders = $paths
            ? array_map('trim', explode(',', $paths))
            : ['app', 'resources', 'routes'];

        return collect($folders)
            ->map(fn ($p) => base_path($p))
            ->filter(fn ($p) => File::isDirectory($p))
            ->values()
            ->all();
    }

    /**
     * Scan .php and .blade.php files for __('...') / @lang('...') / trans('...') keys.
     */
    private function collectKeys(array $paths): array
    {
        $keys = [];

        // Matches: __('key'), __("key"), @lang('key'), trans('key'), trans_choice('key', ...)
        // Group 1 = single-quoted content, Group 2 = double-quoted content
        $pattern = '/(?:__|@lang|trans|trans_choice)\(\s*(?:\'((?:[^\'\\\\]|\\\\.)*)\'|"((?:[^"\\\\]|\\\\.)*)")/';

        foreach ($paths as $path) {
            $files = collect(File::allFiles($path))
                ->filter(fn ($f) => str_ends_with($f->getFilename(), '.php'));

            foreach ($files as $file) {
                $contents = File::get($file->getPathname());

                if (preg_match_all($pattern, $contents, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $m) {
                        $raw = $m[1] !== '' ? $m[1] : ($m[2] ?? '');
                        if ($raw === '') {
                            continue;
                        }

                        // Unescape \' and \" back to literal characters
                        $key = str_replace(["\\'", '\\"'], ["'", '"'], $raw);

                        // Skip keys that look like dotted PHP array keys (e.g. validation.required)
                        // These belong in PHP lang files, not JSON. Remove this block if you want them too.
                        if (preg_match('/^[a-z0-9_-]+(\.[a-z0-9_.-]+)+$/i', $key) && !str_contains($key, ' ')) {
                            continue;
                        }

                        $keys[$key] = true;
                    }
                }
            }
        }

        return array_keys($keys);
    }
}