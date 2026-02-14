<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SubIssueCategory;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Ticket::with(['category', 'subCategory', 'user'])
            ->orderBy('opened_at', 'desc')
            ->paginate(20);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'issue_category_id' => 'required|exists:issue_categories,id',
            'sub_issue_category_id' => 'required|exists:sub_issue_categories,id',
            'order_code' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $order = Order::where('order_code', $data['order_code'])->firstOrFail();

        $exists = SubIssueCategory::where([
            ['id', $data['sub_issue_category_id']],
            ['issue_category_id', $data['issue_category_id']],
        ])->exists();

        if (!$exists) {
            return response()->json([
                'message' => 'Sub-category does not belong to the given main category.',
            ], 422);
        }

        $data['user_id'] = $request->user()->id;
        $ticket = Ticket::create($data);

        return response()->json(
            $ticket->load(['category', 'subCategory', 'order', 'user']),
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
        return $ticket->load(['category', 'subCategory', 'user']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'status' => 'required|in:open,pending,closed',
            'note' => 'nullable|string',
        ]);
        $ticket->update($data);
        return $ticket->load(['category', 'subCategory', 'order', 'user']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
