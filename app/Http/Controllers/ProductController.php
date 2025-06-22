<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('name')->get();

        return Inertia::render('Products/Index', [
            'products' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'trade_price' => 'required|numeric',
            'retail_price' => 'required|numeric',
            'mpn' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:255'
        ]);

        Product::create($request->only('name', 'category', 'quantity', 'trade_price', 'retail_price', 'mpn', 'sku'));

        return redirect()->route('products.index');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index');
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'quantity' => 'required|integer',
            'trade_price' => 'required|numeric',
            'retail_price' => 'required|numeric',
            'mpn' => 'nullable|string',
            'sku' => 'nullable|string',
        ]);

        $product->update($request->only([
            'name',
            'category',
            'quantity',
            'trade_price',
            'retail_price',
            'mpn',
            'sku'
        ]));

        return redirect()->route('products.index');
    }


}
