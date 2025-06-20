<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::getForUser()->first();
        return Inertia::render('Settings/Index', ['settings' => $settings]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'labor_hours' => 'required|numeric',
            'labor_cost_per_hour' => 'required|numeric',
            'fixed_overheads' => 'required|numeric',
            'target_profit_margin' => 'required|numeric',
            'llm_provider' => 'required|string',
            'api_key' => 'nullable|string',
            'model_name' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();

        Setting::updateOrCreate(
            ['user_id' => auth()->id()], // 🔍 Find by user_id
            $validated // ✅ Update with validated data
        );

        return redirect()->back()->with('success', 'Settings updated.');
    }

    public function testConnection(Request $request)
{
    $provider = $request->provider;
    $apiKey = $request->api_key;

    try {
        if ($provider === 'openai') {
            $response = Http::withToken($apiKey)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => 'ping']
                ]
            ]);

            if ($response->successful()) {
                return response()->json(['status' => 'success']);
            }
        }

        // Add handling for Hugging Face / self-hosted here...

        return response()->json(['status' => 'fail'], 422);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}

}
