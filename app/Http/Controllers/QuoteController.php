<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Quote;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class QuoteController extends Controller
{
    public function index()
    {
        $quotes = Quote::orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return Inertia::render('Quote/Index', [
            'quotes' => $quotes,
        ]);
    }

    public function create()
    {
        $products = Product::all();
        $settings = Setting::first();

        return inertia('Quote/Create', [
            'products' => $products,
            'settings' => $settings,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_address' => 'nullable|string|max:1000',
            'products' => 'required|array|min:1',
            'products.*' => 'exists:products,id',
            'labor_hours' => 'required|numeric|min:0.01',
            'labor_cost_per_hour' => 'required|numeric|min:0.01',
            'fixed_overheads' => 'nullable|numeric|min:0',
            'target_profit_margin' => 'nullable|numeric|min:0',
        ]);

        // Fetch products
        $products = Product::whereIn('id', $validated['products'])->get();

        $total_cost = $products->sum('trade_price');
        $total_sell = $products->sum('retail_price');
        $gross_profit = $total_sell - $total_cost;

        $labor_cost = $validated['labor_hours'] * $validated['labor_cost_per_hour'];
        $fixed_overheads = $validated['fixed_overheads'] ?? 0;

        $net_profit = $gross_profit - $labor_cost - $fixed_overheads;
        $calculated_margin = $total_sell > 0 ? round(($net_profit / $total_sell) * 100, 2) : 0;

        // Health status based on margin
        $target_margin = $validated['target_profit_margin'] ?? 20;
        $health_status = 'green';
        if ($calculated_margin < $target_margin * 0.5) {
            $health_status = 'red';
        } elseif ($calculated_margin < $target_margin * 0.9) {
            $health_status = 'amber';
        }

        $quote = Quote::create([
            'customer_name' => $validated['customer_name'],
            'customer_address' => $validated['customer_address'] ?? null,
            'user_id' => Auth::id(),
            'products' => $validated['products'], // stored as JSON array
            'labor_hours' => $validated['labor_hours'],
            'labor_cost_per_hour' => $validated['labor_cost_per_hour'],
            'fixed_overheads' => $fixed_overheads,
            'target_profit_margin' => $validated['target_profit_margin'],
            'calculated_margin' => $calculated_margin,
            'total_profit' => $net_profit,
            'health_status' => $health_status,
        ]);

        return redirect()->route('quotes.show', $quote->id);
    }

    public function show(Quote $quote)
    {
        // Decode the stored product IDs
        $productIds = $quote->products ?? [];

        // Fetch the product details
        $products = Product::whereIn('id', $productIds)->get();

        return Inertia::render('Quote/Show', [
            'quote' => $quote,
            'products' => $products,
        ]);
    }

    public function generateSuggestion(Request $request, Quote $quote)
    {
        $request->validate([
            'context' => 'nullable|string|max:1000',
        ]);

        $context = $request->input('context');

        $settings = Setting::where('user_id', Auth::id())->firstOrFail();

        $productIds = $quote->products ?? [];
        $products = Product::whereIn('id', $productIds)->get();

        $input = [
            'customer' => $quote->customer_name,
            'address' => $quote->customer_address,
            'products' => $products->map(fn($p) => [
                'name' => $p->name,
                'sku' => $p->sku,
                'cost' => $p->trade_price,
                'sell' => $p->retail_price,
            ]),
            'labor_hours' => $quote->labor_hours,
            'labor_cost' => $quote->labor_cost_per_hour,
            'fixed_overheads' => $quote->fixed_overheads,
            'target_profit_margin' => $quote->target_profit_margin,
            'calculated_margin' => $quote->calculated_margin,
            'health_status' => $quote->health_status,
        ];

        $prompt = "Given the quote data below, provide details for the following. Below is the instructions. Use your own headings:\n
                    - Adjustments to meet target margins\n
                    - Labor or resource allocation improvements\n
                    - Suggested product swaps if needed\n
                    - profitability summary\n";

        if (!empty($context)) {
            $prompt .= "Additional context to consider:\n" . $context . "\n";
        }

        $prompt .= "\nQuote Data:\n" . json_encode($input, JSON_PRETTY_PRINT);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $settings->api_key,
            ])->post('https://api.openai.com/v1/chat/completions', [
                        'model' => "gpt-4.1",
                        'messages' => [
                            ['role' => 'system', 'content' => 'You are a helpful business analyst. You starts with "Here’s an analysis and recommendations based on your quote data:"'],
                            ['role' => 'user', 'content' => $prompt],
                        ],
                        'max_tokens' => 500
                    ]);
        } catch (\Exception $e) {
            return redirect()->route('quotes.show', $quote->id)->withErrors(['error' => 'Failed to generate AI suggestions: ' . $e->getMessage()]);
        }


        $aiText = $response['choices'][0]['message']['content'] ?? 'AI response unavailable.';
        $quote->ai_suggestions = $aiText;
        $quote->ai_model_used = $settings->llm_model;
        $quote->last_ai_feedback = now();
        $quote->save();

        return redirect()->route('quotes.show', $quote->id);
    }
}