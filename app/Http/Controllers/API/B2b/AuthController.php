<?php

namespace App\Http\Controllers\API\B2b;

use App\Http\Controllers\Controller;
use App\Models\B2bClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Register a new B2B client
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => 'required|email|unique:b2b_clients,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'tax_number' => 'nullable|string|max:50',
            'address' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $b2bClient = B2bClient::create([
                'company_name' => $request->company_name,
                'contact_person' => $request->contact_person,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'tax_number' => $request->tax_number,
                'address' => $request->address,
            ]);

            // Generate API token
            $token = $b2bClient->createToken('b2b-client-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'data' => [
                    'client' => [
                        'id' => $b2bClient->id,
                        'company_name' => $b2bClient->company_name,
                        'contact_person' => $b2bClient->contact_person,
                        'email' => $b2bClient->email,
                        'phone' => $b2bClient->phone,
                        'tax_number' => $b2bClient->tax_number,
                        'address' => $b2bClient->address,
                        'pricing_tier' => $b2bClient->pricingTier,
                        'created_at' => $b2bClient->created_at,
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer',
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login B2B client
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Attempt to authenticate
        $b2bClient = B2bClient::where('email', $request->email)->first();

        if (!$b2bClient || !Hash::check($request->password, $b2bClient->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }
        if (!$b2bClient->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is not active. Please contact support.',
            ], 403);
        }

        // Generate API token
        $token = $b2bClient->createToken('b2b-client-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'client' => [
                    'id' => $b2bClient->id,
                    'company_name' => $b2bClient->company_name,
                    'contact_person' => $b2bClient->contact_person,
                    'email' => $b2bClient->email,
                    'phone' => $b2bClient->phone,
                    'tax_number' => $b2bClient->tax_number,
                    'address' => $b2bClient->address,
                    'credit_limit' => $b2bClient->credit_limit,
                    'status' => $b2bClient->status,
                    'pricing_tier' => $b2bClient->pricingTier,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ]
        ], 200);
    }

    /**
     * Logout B2B client (revoke current token)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            // Get the authenticated user
            $user = $request->user('b2b');

            $user->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout successful'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout from all devices (revoke all tokens)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logoutAll(Request $request)
    {
        try {
            // Revoke all tokens
            $request->user('b2b')->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logged out from all devices successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get authenticated B2B client profile
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile(Request $request)
    {
        $client = $request->user('b2b');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $client->id,
                'company_name' => $client->company_name,
                'contact_person' => $client->contact_person,
                'email' => $client->email,
                'phone' => $client->phone,
                'tax_number' => $client->tax_number,
                'address' => $client->address,
                'pricing_tier' => $client->pricingTier,
                'created_at' => $client->created_at,
            ]
        ], 200);
    }

    /**
     * Update B2B client profile
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $client = $request->user('b2b');

        $validator = Validator::make($request->all(), [
            'company_name' => 'sometimes|string|max:255',
            'contact_person' => 'sometimes|string|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'tax_number' => 'sometimes|nullable|string|max:50',
            'address' => 'sometimes|nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $client->update($request->only([
                'company_name',
                'contact_person',
                'phone',
                'tax_number',
                'address'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'id' => $client->id,
                    'company_name' => $client->company_name,
                    'contact_person' => $client->contact_person,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'tax_number' => $client->tax_number,
                    'address' => $client->address,
                    'pricing_tier' => $client->pricingTier,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $client = $request->user('b2b');

        // Verify current password
        if (!Hash::check($request->current_password, $client->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
            ], 422);
        }

        try {
            $client->update([
                'password' => Hash::make($request->new_password)
            ]);

            // Optionally revoke all other tokens
            $client->tokens()->where('id', '!=', $request->user('b2b')->currentAccessToken()->id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Password change failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
