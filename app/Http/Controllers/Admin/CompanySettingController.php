<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Support\LangHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CompanySettingController extends Controller
{
    public function edit()
    {
        $setting = CompanySetting::current();
        $locales = LangHelper::localesWithNames();

        return view('admin.site-setting', compact('setting', 'locales'));
    }

    public function update(Request $request)
    {
        $setting = CompanySetting::current();
        $locales = LangHelper::availableLocales();

        $rules = [
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['nullable', 'email', 'max:255'],
            'mobile'          => ['nullable', 'string', 'max:20'],
            'address'         => ['nullable', 'string', 'max:1000'],
            'logo'            => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'login_cover'     => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'favicon'         => ['nullable', 'max:2048'],
            'currency_symbol' => ['nullable', 'array'],
        ];

        foreach ($locales as $loc) {
            $rules["currency_symbol.$loc"] = ['nullable', 'string', 'max:10'];
        }

        $validated = $request->validate($rules);

        if ($request->hasFile('logo')) {
            if ($setting->logo) {
                Storage::disk('public')->delete('company/' . $setting->logo);
            }

            $file = $request->file('logo');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('company', $filename, 'public');
            $validated['logo'] = $filename;
        }

        if ($request->hasFile('login_cover')) {
            if ($setting->login_cover) {
                Storage::disk('public')->delete('company/' . $setting->login_cover);
            }

            $file = $request->file('login_cover');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('company', $filename, 'public');
            $validated['login_cover'] = $filename;
        }

        if ($request->hasFile('favicon')) {
            if ($setting->favicon) {
                Storage::disk('public')->delete('company/' . $setting->favicon);
            }

            $file = $request->file('favicon');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('company', $filename, 'public');
            $validated['favicon'] = $filename;
        }

        $symbolsInput = $request->input('currency_symbol', []);
        $symbols = [];
        foreach ($locales as $loc) {
        $val = $symbolsInput[$loc] ?? null;
            $symbols[$loc] = ($val !== null && $val !== '') ? $val : null;
        }

        unset($validated['currency_symbol']);
        $validated['currency_symbols'] = $symbols;

        $validated['created_by'] = Auth::id();

        $setting->update($validated);

        Cache::forget('company_setting');

        return redirect()->route('admin.site.edit')->with('success', __('Site settings updated successfully.'));
    }
}