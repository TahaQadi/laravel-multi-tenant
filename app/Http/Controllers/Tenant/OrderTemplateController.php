<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\OrderTemplate;
use App\Models\OrderTemplateItem;
use App\Models\Product;
use App\Services\PricingResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OrderTemplateController extends Controller
{
    public function index()
    {
        $templates = OrderTemplate::where('user_id', Auth::id())
            ->withCount('items')
            ->latest()
            ->paginate(10);

        return Inertia::render('tenant/templates/Index', [
            'templates' => $templates,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $template = OrderTemplate::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'scope' => 'user',
        ]);

        return redirect()->back()->with('success', 'Template created')->with('template_id', $template->id);
    }

    public function show(OrderTemplate $template)
    {
        $this->authorizeOwnership($template);

        $template->load(['items.product.images']);

        return Inertia::render('tenant/templates/Show', [
            'template' => $template,
        ]);
    }

    public function addItem(Request $request, OrderTemplate $template)
    {
        $this->authorizeOwnership($template);

        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'default_qty' => 'required|integer|min:1',
        ]);

        $item = OrderTemplateItem::updateOrCreate(
            [
                'order_template_id' => $template->id,
                'product_id' => $data['product_id'],
            ],
            [
                'default_qty' => $data['default_qty'],
            ]
        );

        return redirect()->back()->with('success', 'Item saved');
    }

    public function removeItem(OrderTemplate $template, OrderTemplateItem $item)
    {
        $this->authorizeOwnership($template);

        if ($item->order_template_id !== $template->id) {
            abort(403);
        }

        $item->delete();

        return redirect()->back()->with('success', 'Item removed');
    }

    public function applyToCart(Request $request, OrderTemplate $template)
    {
        $this->authorizeOwnership($template);

        $template->load('items');

        $cart = $this->getCart($request);
        $pricingResolver = new PricingResolver();

        foreach ($template->items as $templateItem) {
            $product = Product::find($templateItem->product_id);
            if (!$product) {
                continue;
            }

            $resolved = $pricingResolver->resolveForUserAndProduct(Auth::user(), $product);

            $quantity = max($templateItem->default_qty, $resolved['min_qty']);
            if ($resolved['pack_multiple'] > 1) {
                $remainder = $quantity % $resolved['pack_multiple'];
                if ($remainder !== 0) {
                    $quantity += ($resolved['pack_multiple'] - $remainder);
                }
            }

            $existing = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->first();

            if ($existing) {
                $newQty = $existing->quantity + $quantity;
                $existing->update(['quantity' => $newQty]);
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $resolved['price'],
                ]);
            }
        }

        return redirect()->route('cart.index')->with('success', 'Template applied to cart');
    }

    private function authorizeOwnership(OrderTemplate $template): void
    {
        if ($template->user_id !== Auth::id()) {
            abort(403);
        }
    }

    private function getCart(Request $request)
    {
        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())->first();
            if (!$cart) {
                $cart = Cart::create(['user_id' => Auth::id()]);
            }
            return $cart;
        }

        if (!$request->session()->has('session_id')) {
            $sessionId = uniqid('session_', true);
            $request->session()->put('session_id', $sessionId);
        } else {
            $sessionId = $request->session()->get('session_id');
        }

        $cart = Cart::where('session_id', $sessionId)->first();
        if (!$cart) {
            $cart = Cart::create(['session_id' => $sessionId]);
        }
        return $cart;
    }
}

