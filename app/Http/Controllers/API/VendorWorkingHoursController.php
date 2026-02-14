<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\VendorWorkingHours;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VendorWorkingHoursController extends Controller
{
    /**
     * Display a listing of working hours for a specific vendor
     */
    public function index(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id'
        ]);

        $workingHours = VendorWorkingHours::where('vendor_id', $request->vendor_id)
            ->orderBy('day_of_week')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Working hours retrieved successfully',
            'data' => $workingHours
        ]);
    }

    /**
     * Store a newly created working hours
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendor_id' => 'required|exists:vendors,id',
            'day_of_week' => 'required|integer|between:0,6',
            'day_en' => 'required|string',
            'day_ar' => 'required|string',
            'open_time' => 'nullable|date_format:H:i',
            'close_time' => 'nullable|date_format:H:i',
            'is_closed' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if working hours already exist for this vendor and day
        $existing = VendorWorkingHours::where('vendor_id', $request->vendor_id)
            ->where('day_of_week', $request->day_of_week)
            ->first();

        if ($existing) {
            return response()->json([
                'status' => false,
                'message' => 'Working hours for this day already exist'
            ], 409);
        }

        $workingHours = VendorWorkingHours::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Working hours created successfully',
            'data' => $workingHours
        ], 201);
    }

    /**
     * Display the specified working hours
     */
    public function show($id)
    {
        $workingHours = VendorWorkingHours::with('vendor')->findOrFail($id);

        return response()->json([
            'status' => true,
            'message' => 'Working hours retrieved successfully',
            'data' => $workingHours
        ]);
    }

    /**
     * Update the specified working hours
     */
    public function update(Request $request, $id)
    {
        $workingHours = VendorWorkingHours::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'day_of_week' => 'sometimes|integer|between:0,6',
            'day_en' => 'sometimes|string',
            'day_ar' => 'sometimes|string',
            'open_time' => 'nullable|date_format:H:i',
            'close_time' => 'nullable|date_format:H:i',
            'is_closed' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if working hours already exist for this vendor and day (excluding current record)
        if ($request->has('day_of_week')) {
            $existing = VendorWorkingHours::where('vendor_id', $workingHours->vendor_id)
                ->where('day_of_week', $request->day_of_week)
                ->where('id', '!=', $id)
                ->first();

            if ($existing) {
                return response()->json([
                    'status' => false,
                    'message' => 'Working hours for this day already exist'
                ], 409);
            }
        }

        $workingHours->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Working hours updated successfully',
            'data' => $workingHours
        ]);
    }

    /**
     * Remove the specified working hours
     */
    public function destroy($id)
    {
        $workingHours = VendorWorkingHours::findOrFail($id);
        $workingHours->delete();

        return response()->json([
            'status' => true,
            'message' => 'Working hours deleted successfully'
        ]);
    }

    /**
     * Get all working hours for all vendors
     */
    public function getAllWorkingHours()
    {
        $workingHours = VendorWorkingHours::with('vendor')
            ->orderBy('vendor_id')
            ->orderBy('day_of_week')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'All working hours retrieved successfully',
            'data' => $workingHours
        ]);
    }

    /**
     * Get working hours for multiple vendors
     */
    public function getMultipleVendorsWorkingHours(Request $request)
    {
        $request->validate([
            'vendor_ids' => 'required|array',
            'vendor_ids.*' => 'exists:vendors,id'
        ]);

        $workingHours = VendorWorkingHours::with('vendor')
            ->whereIn('vendor_id', $request->vendor_ids)
            ->orderBy('vendor_id')
            ->orderBy('day_of_week')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Working hours retrieved successfully',
            'data' => $workingHours
        ]);
    }
}
