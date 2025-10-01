<?php

namespace App\Http\Controllers\Tenant\Manage;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ContractController extends Controller
{
    public function index()
    {
        $contracts = Contract::with('user')
            ->latest('start_at')
            ->paginate(15);

        return Inertia::render('tenant/manage/contracts/Index', [
            'contracts' => $contracts,
        ]);
    }

    public function create()
    {
        return Inertia::render('tenant/manage/contracts/Create', [
            'users' => User::select('id', 'name', 'email')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'status' => 'required|string|in:active,draft,expired',
        ]);

        $contract = Contract::create($data);

        return redirect()->route('manage.contract.index')->with('success', 'Contract created');
    }

    public function show(Contract $contract)
    {
        $contract->load(['user', 'items.product']);

        return Inertia::render('tenant/manage/contracts/Show', [
            'contract' => $contract,
        ]);
    }

    public function uploadItems(Request $request, Contract $contract)
    {
        $request->validate([
            'csv' => 'required|file|mimes:csv,txt',
        ]);

        $path = $request->file('csv')->getRealPath();
        $rows = array_map('str_getcsv', file($path));
        // Expect header: sku,price,min_qty,pack_multiple
        $header = array_map('trim', array_shift($rows));

        $indexes = [
            'sku' => array_search('sku', $header),
            'price' => array_search('price', $header),
            'min_qty' => array_search('min_qty', $header),
            'pack_multiple' => array_search('pack_multiple', $header),
        ];

        DB::transaction(function () use ($rows, $indexes, $contract) {
            foreach ($rows as $row) {
                if (!isset($row[$indexes['sku']])) {
                    continue;
                }
                $sku = trim($row[$indexes['sku']]);
                $product = Product::where('sku', $sku)->first();
                if (!$product) {
                    continue;
                }

                $price = isset($row[$indexes['price']]) ? (float) $row[$indexes['price']] : null;
                if ($price === null) {
                    continue;
                }
                $minQty = isset($row[$indexes['min_qty']]) ? (int) $row[$indexes['min_qty']] : 1;
                $packMultiple = isset($row[$indexes['pack_multiple']]) ? (int) $row[$indexes['pack_multiple']] : 1;

                ContractItem::updateOrCreate(
                    [
                        'contract_id' => $contract->id,
                        'product_id' => $product->id,
                    ],
                    [
                        'price' => $price,
                        'min_qty' => max(1, $minQty),
                        'pack_multiple' => max(1, $packMultiple),
                    ]
                );
            }
        });

        return redirect()->back()->with('success', 'Items uploaded');
    }
}

