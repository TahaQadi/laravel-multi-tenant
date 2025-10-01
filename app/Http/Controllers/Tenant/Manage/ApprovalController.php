<?php

namespace App\Http\Controllers\Tenant\Manage;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ApprovalController extends Controller
{
    public function index()
    {
        $approvals = ApprovalRequest::with(['order', 'approver'])
            ->latest()
            ->paginate(20);

        return Inertia::render('tenant/manage/approvals/Index', [
            'approvals' => $approvals,
        ]);
    }

    public function decide(Request $request, ApprovalRequest $approval)
    {
        $data = $request->validate([
            'decision' => 'required|string|in:approved,rejected',
        ]);

        $approval->state = $data['decision'];
        $approval->decided_at = now();
        $approval->save();

        // Update order status
        $order = $approval->order;
        $order->status = $data['decision'] === 'approved' ? 'approved' : 'rejected';
        $order->save();

        return redirect()->back()->with('success', 'Decision saved.');
    }
}

