<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\PricingResolver;
use App\Services\ApprovalService;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OrderController extends Controller
{
    /**
     * Split an order by delivery date or address (basic example).
     */
    public function split(Request $request, Order $order)
    {
        $request->validate([
            'by' => 'required|in:delivery_address,delivery_date',
        ]);

        $order->load('items');
        $groups = [];
        foreach ($order->items as $item) {
            $key = $request->by === 'delivery_address' ? ($item->delivery_address ?? 'unknown') : ($item->delivery_date ?? 'unknown');
            $groups[$key] = ($groups[$key] ?? 0) + $item->subtotal;
        }

        return response()->json([
            'groups' => $groups,
        ]);
    }
    /**
     * Display the checkout page
     */
    public function checkout(Request $request)
    {
        // Get the cart for the current user or session
        $cart = $this->getCart($request);

        // Load cart with items and product details
        $cart->load(['items.product.images']);

        // Provide delivery locations if authenticated
        $deliveryLocations = [];
        if (Auth::check()) {
            $deliveryLocations = \App\Models\DeliveryLocation::where('user_id', Auth::id())->get();
        }

        // Check if cart is empty
        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty. Add items before checkout.');
        }

        return Inertia::render('tenant/Payment', [
            'cart' => $cart,
            'cartItems' => $cart->items,
            'user' => Auth::user(),
            'deliveryLocations' => $deliveryLocations,
        ]);
    }

    /**
     * Store a new order
     */
    public function store(Request $request)
    {
        // Validate order data
        $request->validate([
            'billing_name' => 'required|string|max:255',
            'billing_email' => 'required|email|max:255',
            'billing_phone' => 'required|string|max:30',
            'billing_address' => 'required|string|max:255',
            'billing_city' => 'required|string|max:100',
            'billing_state' => 'required|string|max:100',
            'billing_country' => 'required|string|max:100',
            'billing_zipcode' => 'required|string|max:20',
            'shipping_name' => 'required|string|max:255',
            'shipping_address' => 'required|string|max:255',
            'shipping_city' => 'required|string|max:100',
            'shipping_state' => 'required|string|max:100',
            'shipping_country' => 'required|string|max:100',
            'shipping_zipcode' => 'required|string|max:20',
            'payment_method' => 'required|in:Bank Transfer,paypal',
            'notes' => 'nullable|string|max:1000',
            'items' => 'nullable|array',
            'items.*.delivery_address' => 'nullable|string|max:255',
            'items.*.delivery_date' => 'nullable|date',
        ]);

        // Get the cart
        $cart = $this->getCart($request);
        $cart->load('items.product');

        // Check if cart is empty
        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty. Add items before checkout.');
        }

        // Verify constraints and inventory
        $pricingResolver = new PricingResolver();
        foreach ($cart->items as $item) {
            $product = $item->product;
            $resolved = $pricingResolver->resolveForUserAndProduct(Auth::user(), $product);

            // Enforce min and pack multiples
            $quantity = max($item->quantity, $resolved['min_qty']);
            if ($resolved['pack_multiple'] > 1) {
                $remainder = $quantity % $resolved['pack_multiple'];
                if ($remainder !== 0) {
                    $quantity += ($resolved['pack_multiple'] - $remainder);
                }
            }

            if ($quantity !== $item->quantity) {
                return redirect()->route('cart.index')->with('error', 'Cart contains items not meeting contract constraints. Please review quantities.');
            }

            $inventory = Inventory::where('product_id', $product->id)->first();
            if ($inventory) {
                $available = max(0, $inventory->on_hand - $inventory->reserved);
                if ($quantity > $available && !$inventory->backorderable) {
                    return redirect()->route('cart.index')->with('error', "Not enough stock for {$product->name}.");
                }
            } else if ($quantity > $product->stock) {
                return redirect()->route('cart.index')->with('error', "Not enough stock for {$product->name}. Available: {$product->stock}");
            }
        }

        try {
            DB::beginTransaction();

            // Calculate order total
            $subtotal = 0;
            foreach ($cart->items as $item) {
                $subtotal += $item->price * $item->quantity;
            }

            // Apply taxes and shipping (simplified)
            $taxRate = 0.08; // 8%
            $shippingCost = 10.00;
            $taxAmount = $subtotal * $taxRate;
            $total = $subtotal + $taxAmount + $shippingCost;

            // Generate a unique order number
            $orderNumber = 'INV-' . date('Ymd') . '-' . uniqid();

            // Create the order
            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => Auth::id(),
                'total' => $total,
                'status' => 'pending',
                'notes' => $request->notes,
                'billing_name' => $request->billing_name,
                'billing_email' => $request->billing_email,
                'billing_phone' => $request->billing_phone,
                'billing_address' => $request->billing_address,
                'billing_city' => $request->billing_city,
                'billing_state' => $request->billing_state,
                'billing_country' => $request->billing_country,
                'billing_zipcode' => $request->billing_zipcode,
                'shipping_name' => $request->shipping_name,
                'shipping_address' => $request->shipping_address,
                'shipping_city' => $request->shipping_city,
                'shipping_state' => $request->shipping_state,
                'shipping_country' => $request->shipping_country,
                'shipping_zipcode' => $request->shipping_zipcode,
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
            ]);

            // Create order items and update inventory
            foreach ($cart->items as $item) {
                // Create order item
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => $item->price * $item->quantity,
                    'delivery_address' => $request->input("items.{$item->id}.delivery_address"),
                    'delivery_date' => $request->input("items.{$item->id}.delivery_date"),
                ]);

                // Update product inventory
                $product = $item->product;
                $product->stock -= $item->quantity;
                $product->save();
            }

            // Evaluate approvals
            $approvalService = new ApprovalService();
            $approval = $approvalService->evaluate($order);
            if ($approval) {
                $order->status = 'pending_approval';
                $order->save();
            }

            // Clear the cart
            $cart->items()->delete();

            // If using PayPal, we would redirect to PayPal here
            // For this example, we'll just redirect to order confirmation

            DB::commit();

            $message = $approval ? 'Your order has been submitted and is pending approval.' : 'Your order has been placed successfully!';
            return redirect()->route('orders.confirmation', $order->id)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'An error occurred while processing your order: ' . $e->getMessage());
        }
    }

    /**
     * Display the order confirmation page
     */
    public function confirmation(Order $order)
    {
        // Check if the order belongs to the current user
        if (Auth::id() && $order->user_id !== Auth::id()) {
            abort(403);
        }

        // Load order items with products
        $order->load('items.product');

        return Inertia::render('tenant/OrderConfirmation', [
            'order' => $order,
            'orderItems' => $order->items
        ]);
    }

    /**
     * Get or create a cart for the current user or session
     */
    private function getCart(Request $request)
    {
        if (Auth::check()) {
            // Get cart for authenticated user
            $cart = Cart::where('user_id', Auth::id())->first();

            if (!$cart) {
                // Create a new cart if the user doesn't have one
                $cart = Cart::create([
                    'user_id' => Auth::id()
                ]);
            }

            return $cart;
        } else {
            // For guest users, get or create cart by session ID
            if (!$request->session()->has('session_id')) {
                $sessionId = uniqid('session_', true);
                $request->session()->put('session_id', $sessionId);
            } else {
                $sessionId = $request->session()->get('session_id');
            }

            $cart = Cart::where('session_id', $sessionId)->first();

            if (!$cart) {
                // Create a new cart for the session
                $cart = Cart::create([
                    'session_id' => $sessionId
                ]);
            }

            return $cart;
        }
    }
}
