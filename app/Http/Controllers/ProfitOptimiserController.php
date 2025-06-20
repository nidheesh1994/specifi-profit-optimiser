<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;

class ProfitOptimiserController extends Controller
{
    public function index()
    {
        $products = Product::all();
        $settings = Setting::first();

        return inertia('ProfitOptimiser/Index', [
            'products' => $products,
            'settings' => $settings,
        ]);
    }
}