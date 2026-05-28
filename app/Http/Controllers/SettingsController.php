<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all();
        return response()->json($settings);
    }

    public function update(Request $request)
    {
        $settings = $request->all();

        foreach ($settings as $setting) {
            $name = $setting['name'] ?? null;
            $value = $setting['value'] ?? null;

            if ($name) {
                Setting::where('name', $name)->update(['value' => $value]);
            }
        }

        return response()->json(['message' => 'Настройки успешно обновлены']);
    }
}
