<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AddOn;
use Illuminate\Http\Request;

class AddOnController extends Controller
{
    /**
     * GET /api/add-ons
     */
    public function index()
    {
        // جلب كل الإضافات
        $addOns = AddOn::all();

        return response()->json([
            'status' => true,
            'data' => $addOns
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
