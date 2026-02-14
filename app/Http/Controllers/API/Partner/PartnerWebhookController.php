<?php

namespace App\Http\Controllers\API\Partner;

use App\Http\Controllers\Controller;
use App\Http\Middleware\ValidatePartner;
use App\Models\PartnerWebhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PartnerWebhookController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $source = ValidatePartner::getSource($request->header('X-Source'));

        $webhooks = PartnerWebhook::where('source_secret', $source['secret'])->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => true,
            'data' => $webhooks
        ]);
    }

    /**
     * Store a newly created partner webhook.
     */
    public function store(Request $request): JsonResponse
    {
        $source = ValidatePartner::getSource($request->header('X-Source'));

        $webhooks = PartnerWebhook::where('source_secret', $source['secret'])->count();

        if($webhooks >= 10){
            return response()->json([
                'status' => false,
                'message' => 'Partner can only have 5 webhooks'
            ], 400);
        }

        $validator = \Validator::make($request->all(), [
            'method' => ['required', Rule::in(['GET', 'POST'])],
            'url' => 'required|url|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        $data['source_name'] = $source['name'];
        $data['source_secret'] = $source['secret'];

        $webhook = PartnerWebhook::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Partner webhook created successfully',
            'data' => $webhook
        ], 201);
    }

    /**
     * Display the specified partner webhook.
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $source = ValidatePartner::getSource($request->header('X-Source'));

        $webhook = PartnerWebhook::where('source_secret', $source['secret'])->first($id);

        if (!$webhook) {
            return response()->json([
                'status' => false,
                'message' => 'Partner webhook not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $webhook
        ]);
    }

    /**
     * Update the specified partner webhook.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $source = ValidatePartner::getSource($request->header('X-Source'));

        $webhook = PartnerWebhook::where('source_secret', $source['secret'])->where('id', $id)->first();

        if (!$webhook) {
            return response()->json([
                'status' => false,
                'message' => 'Partner webhook not found'
            ], 404);
        }

        $validator = \Validator::make($request->all(), [
            'method' => ['sometimes', 'required', Rule::in(['GET', 'POST'])],
            'url' => 'sometimes|required|url|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $webhook->update($validator->validated());

        return response()->json([
            'status' => true,
            'message' => 'Partner webhook updated successfully',
            'data' => $webhook
        ]);
    }

    /**
     * Remove the specified partner webhook.
     */
    public function destroy(string $id, Request $request): JsonResponse
    {
        $source = ValidatePartner::getSource($request->header('X-Source'));

        $webhook = PartnerWebhook::where('source_secret', $source['secret'])->where('id', $id)->first();
        if (!$webhook) {
            return response()->json([
                'status' => false,
                'message' => 'Partner webhook not found'
            ], 404);
        }

        $webhook->delete();

        return response()->json([
            'status' => true,
            'message' => 'Partner webhook deleted successfully'
        ]);
    }
}
