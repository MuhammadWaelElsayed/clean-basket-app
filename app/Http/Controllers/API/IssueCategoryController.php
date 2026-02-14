<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\IssueCategory;
use Illuminate\Http\Request;

class IssueCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return IssueCategory::with('subCategories')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate(['name'=>'required|string', 'name_ar'=>'required|string']);
         return IssueCategory::create($data);
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
    public function update(Request $request, IssueCategory $issueCategory)
    {
        $data = $request->validate(['name'=>'required|string', 'name_ar'=>'required|string']);
        $issueCategory->update($data);
        return $issueCategory;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(IssueCategory $issueCategory)
    {
        $issueCategory->delete();
        return response()->noContent();
    }
}
