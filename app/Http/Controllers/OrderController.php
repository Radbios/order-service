<?php

namespace App\Http\Controllers;

use App\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{
    public function index()
    {
        return Order::where("user_id", Auth::user()->id)->get();
    }

    public function my_top_category()
    {
        $categories = OrderItem::whereHas('order', function ($query) {
            $query->where('user_id', Auth::user()->id);
        })->get()->map(function($order) {
            $response = Http::get('127.0.0.1:8000/api/products/' . $order['product_id']);
            $product = $response->json("data");
            return (object)[
                "category" => $product['category'],
                "quantity" => $order->quantity
            ];
        });

        $grouped = $categories->groupBy('category')->map(function ($items) {
            return $items->sum('quantity');
        });

        $topCategory = $grouped->sortDesc()->keys()->first();

        return response()->json([
            'category' => $topCategory,
        ]);
    }

    public function top_category()
    {
        $categories = OrderItem::get()->map(function($order) {
            $response = Http::get('127.0.0.1:8000/api/products/' . $order['product_id']);
            $product = $response->json("data");
            return (object)[
                "category" => $product['category'],
                "quantity" => $order->quantity
            ];
        });

        $grouped = $categories->groupBy('category')->map(function ($items) {
            return $items->sum('quantity');
        });

        $topCategory = $grouped->sortDesc()->keys()->first();

        return response()->json([
            'category' => $topCategory,
        ]);
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
        if(!$order) {
            return response()->json([
                "status" => "error",
                "message" => "Ordem não encontrada"
            ]);
        }

        $order->load("items");
        return $order;
    }

    public function update(Request $request, Order $order)
    {
        if(!$order) {
            return response()->json([
                "status" => "error",
                "message" => "Ordem não encontrada"
            ]);
        }

        $order->update([
            "status" => "paid"
        ]);

        $order->refresh();

        return $order;
    }
}
