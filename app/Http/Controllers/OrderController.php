<?php

namespace App\Http\Controllers;

use App\Auth;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{
    public function index()
    {
        return Order::where("user_id", Auth::user()->id)->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'shipping_cost' => ['required', 'numeric', 'min:0'],
            'shipping_service' => ['required', 'string', 'max:100'],
            'payment_type' => ["required"]
        ]);

        $items = $request->input('items'); // [{product_id, quantity}]
        $shippingCost = $request->input('shipping_cost', 0);

        $total = 0;

        $order = Order::create([
            'user_id' => Auth::user()->id,
            'payment_type' => $request->input("payment_type"),
            'status' => 'pending',
            'shipping_service' => $request->input('shipping_service'),
            'shipping_cost' => $shippingCost,
            'total' => 0, // temporariamente
        ]);

        foreach ($items as $item) {
            $response = Http::get(env("APP_GATEWAY") . '/catalog/products/' . $item['product_id']);
    
            $product = $response->json('data');

            $subtotal = $product['price'] * $item['quantity'];
            $total += $subtotal;

            $order->items()->create([
                'product_id' => $product['id'],
                'quantity' => $item['quantity'],
                'unit_price' => $product['price'],
            ]);
        }

        $order->update(['total' => $total + $shippingCost]);

        return response()->json(['order' => $order->load('items')], 201);
    }

    public function show(Order $order)
    {
        return $order;
    }

    public function update(Request $request, Order $order)
    {
        $order->update([
            "status" => "paid"
        ]);

        $order->refresh();

        return $order;
    }
}
