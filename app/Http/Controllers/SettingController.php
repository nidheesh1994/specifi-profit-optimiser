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
            'connection_status' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();

        Setting::updateOrCreate(
            ['user_id' => auth()->id()], // 🔍 Find by user_id
            $validated // ✅ Update with validated data
        );

        return redirect()->route('settings.index')->with('success', 'Settings updated.');
    }

    public function testConnection(Request $request)
    {
        $provider = $request->provider;
        $modal_name = $request->model_name;
        $apiKey = $request->api_key;

        try {
            if ($provider === 'openai') {
                $response = Http::withToken($apiKey)->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $modal_name,
                    'messages' => [
                        ['role' => 'system', 'content' => 'ping']
                    ]
                ]);

                $status = $response->successful() ? 'success' : 'error';
                // dd($status);
            }

            // Add handling for Hugging Face / self-hosted here...

            return redirect()->back()->with('connection_status', $status);
        } catch (\Exception $e) {
            return redirect()->back()->with('connection_status', 'error');
        }
    }

}
