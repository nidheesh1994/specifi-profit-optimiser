<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    public function create()
    {
        $products = Product::all();
        $settings = Setting::first();

        return inertia('Quote/Create', [
            'products' => $products,
            'settings' => $settings,
        ]);
    }
}