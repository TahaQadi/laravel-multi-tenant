<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\DeliveryLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DeliveryLocationController extends Controller
{
    public function index()
    {
        $locations = DeliveryLocation::where('user_id', Auth::id())->latest()->paginate(10);
        return Inertia::render('tenant/delivery-locations/Index', [
            'locations' => $locations,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'zipcode' => 'nullable|string|max:20',
        ]);

        DeliveryLocation::create(array_merge($data, ['user_id' => Auth::id()]));

        return redirect()->back()->with('success', 'Location saved');
    }

    public function destroy(DeliveryLocation $location)
    {
        if ($location->user_id !== Auth::id()) {
            abort(403);
        }
        $location->delete();
        return redirect()->back()->with('success', 'Location removed');
    }
}

