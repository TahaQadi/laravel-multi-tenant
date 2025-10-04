<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\StandingOrder;
use App\Models\StandingOrderItem;
use App\Services\StandingOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StandingOrderController extends Controller
{
    public function index()
    {
        $standingOrders = StandingOrder::where('user_id', Auth::id())
            ->withCount('items')
            ->latest()
            ->paginate(10);

        return Inertia::render('tenant/standing-orders/Index', [
            'standingOrders' => $standingOrders,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'schedule_cron' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        $standingOrder = StandingOrder::create([
            'user_id' => Auth::id(),
            'name' => $data['name'],
            'schedule_cron' => $data['schedule_cron'],
            'status' => 'active',
        ]);

        foreach ($data['items'] as $item) {
            StandingOrderItem::create([
                'standing_order_id' => $standingOrder->id,
                'product_id' => $item['product_id'],
                'qty' => $item['qty'],
            ]);
        }

        return redirect()->route('standing-orders.index')->with('success', 'Standing order created');
    }

    public function run(StandingOrder $standingOrder)
    {
        if ($standingOrder->user_id !== Auth::id()) {
            abort(403);
        }
        $service = new StandingOrderService();
        $order = $service->run($standingOrder);
        return redirect()->route('orders.confirmation', $order->id)->with('success', 'Standing order executed');
    }
}

