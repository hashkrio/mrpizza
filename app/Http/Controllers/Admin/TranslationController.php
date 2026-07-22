<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\LangHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;

class TranslationController extends Controller
{
    /**
     * Show the translation manager: every key with its value per locale.
     */
    public function index(Request $request)
    {
        $locales = LangHelper::availableLocales();
        $data    = $this->loadAll($locales);

        // $data['keys'] = ['Save' => ['en' => 'Save', 'pt' => 'Guardar'], ...]
        $rows = $data['keys'];

        // Optional search filter
        $search = trim((string) $request->get('q', ''));
        if ($search !== '') {
            $rows = array_filter($rows, function ($values, $key) use ($search) {
                if (stripos($key, $search) !== false) {
                    return true;
                }
                foreach ($values as $v) {
                    if (stripos((string) $v, $search) !== false) {
                        return true;
                    }
                }
                return false;
            }, ARRAY_FILTER_USE_BOTH);
        }

        ksort($rows);

        return view('admin.translations.index', [
            'locales' => $locales,
            'rows'    => $rows,
            'search'  => $search,
        ]);
    }

    /**
     * Store a brand new key across all locales.
     */
    public function store(Request $request): RedirectResponse
    {
        $locales = LangHelper::availableLocales();

        $rules = [
            'key' => 'required|string|max:1000',
        ];
        foreach ($locales as $loc) {
            $rules["values.$loc"] = 'nullable|string';
        }
        $request->validate($rules);

        $key = $request->input('key');

        foreach ($locales as $loc) {
            $json = $this->readLocale($loc);

            if (array_key_exists($key, $json)) {
                return redirect()->back()->withInput()
                    ->with('error', __('That key already exists. Edit it instead.'));
            }

            $json[$key] = (string) ($request->input("values.$loc") ?? '');
            $this->writeLocale($loc, $json);
        }

        return redirect()->route('admin.translations.index')
            ->with('success', __('Translation key added successfully.'));
    }

    /**
     * Update the values of an existing key (AJAX inline save).
     */
    public function update(Request $request): JsonResponse
    {
        $locales = LangHelper::availableLocales();

        $rules = [
            'key' => 'required|string',
        ];
        foreach ($locales as $loc) {
            $rules["values.$loc"] = 'nullable|string';
        }
        $request->validate($rules);

        $key = $request->input('key');

        foreach ($locales as $loc) {
            $json = $this->readLocale($loc);

            // Key must already exist in at least the base to be an "edit"
            $json[$key] = (string) ($request->input("values.$loc") ?? '');
            $this->writeLocale($loc, $json);
        }

        return response()->json([
            'success' => true,
            'message' => __('Translation updated successfully.'),
        ]);
    }

    /**
     * Rename an existing key across all locales (keeps values).
     */
    public function rename(Request $request): JsonResponse
    {
        $request->validate([
            'old_key' => 'required|string',
            'new_key' => 'required|string|max:1000',
        ]);

        $locales = LangHelper::availableLocales();
        $oldKey  = $request->input('old_key');
        $newKey  = $request->input('new_key');

        if ($oldKey === $newKey) {
            return response()->json(['success' => true, 'message' => __('No change.')]);
        }

        foreach ($locales as $loc) {
            $json = $this->readLocale($loc);

            if (array_key_exists($newKey, $json)) {
                return response()->json([
                    'success' => false,
                    'message' => __('The new key already exists.'),
                ], 422);
            }

            if (array_key_exists($oldKey, $json)) {
                $json[$newKey] = $json[$oldKey];
                unset($json[$oldKey]);
                $this->writeLocale($loc, $json);
            }
        }

        return response()->json([
            'success' => true,
            'message' => __('Key renamed successfully.'),
        ]);
    }

    /**
     * Delete a key from all locales.
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['key' => 'required|string']);

        $locales = LangHelper::availableLocales();
        $key     = $request->input('key');

        foreach ($locales as $loc) {
            $json = $this->readLocale($loc);
            if (array_key_exists($key, $json)) {
                unset($json[$key]);
                $this->writeLocale($loc, $json);
            }
        }

        return response()->json([
            'success' => true,
            'message' => __('Translation key deleted successfully.'),
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  Helpers                                                            */
    /* ------------------------------------------------------------------ */

    /**
     * Load every key from every locale, merged into one map.
     */
    private function loadAll(array $locales): array
    {
        $allKeys = [];
        $perLocale = [];

        foreach ($locales as $loc) {
            $json = $this->readLocale($loc);
            $perLocale[$loc] = $json;
            foreach (array_keys($json) as $k) {
                $allKeys[$k] = true;
            }
        }

        $keys = [];
        foreach (array_keys($allKeys) as $k) {
            $row = [];
            foreach ($locales as $loc) {
                $row[$loc] = $perLocale[$loc][$k] ?? '';
            }
            $keys[$k] = $row;
        }

        return ['keys' => $keys];
    }

    private function localePath(string $locale): string
    {
        return base_path("lang/{$locale}.json");
    }

    private function readLocale(string $locale): array
    {
        $path = $this->localePath($locale);
        if (!File::exists($path)) {
            return [];
        }
        return json_decode(File::get($path), true) ?: [];
    }

    private function writeLocale(string $locale, array $data): void
    {
        ksort($data);
        File::put(
            $this->localePath($locale),
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL
        );
    }
}