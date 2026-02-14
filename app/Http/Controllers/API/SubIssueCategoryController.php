<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SubIssueCategory;
use Illuminate\Http\Request;

class SubIssueCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($issueCategoryId)
    {
        return SubIssueCategory::with('mainCategory')->where('issue_category_id', $issueCategoryId)->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $issueCategoryId)
    {
        $data = $request->validate(['name'=>'required|string', 'name_ar'=>'required|string']);
        $data['issue_category_id'] = $issueCategoryId;
        return SubIssueCategory::create($data);
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
    public function update(Request $request, $issueCategoryId, SubIssueCategory $sub_issue_category)
    {
        abort_unless($sub_issue_category->issue_category_id == $issueCategoryId, 404);
        $sub_issue_category->update($request->validate(['name'=>'required|string', 'name_ar'=>'required|string']));
        return $sub_issue_category;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($issueCategoryId, SubIssueCategory $sub_issue_category)
    {
        abort_unless($sub_issue_category->issue_category_id == $issueCategoryId, 404);
        $sub_issue_category->delete();
        return response()->noContent();
    }
}
