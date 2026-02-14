<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\OrderPriority;
use Illuminate\Http\Request;

class OrderPriorityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       // جلب كل أولويات الطلب
       $priorities = OrderPriority::all();

       // إرجاعها كـ JSON
       return response()->json([
           'data' => $priorities
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
