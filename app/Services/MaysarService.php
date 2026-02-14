<?php

namespace App\Services;

use App\Models\PaymentLog;
use Illuminate\Support\Facades\Log;

class MaysarService
{
    /**
     * refund the amount from maysar
     */
    public function refundPayment(PaymentLog $paymentLog): array
    {
        try {
            // الحصول على تفاصيل الدفع من ميسر للتحقق من المبلغ المتاح
            $paymentDetails = $this->getPaymentDetails($paymentLog->transaction_id);

            // حساب المبلغ المتاح للاسترداد
            $availableAmount = 0;
            if (!empty($paymentDetails)) {
                $totalAmount = $paymentDetails['amount'] ?? 0;
                $refundedAmount = $paymentDetails['refunded'] ?? 0;
                $availableAmount = $totalAmount - $refundedAmount;

                Log::info('Maysar Payment Details', [
                    'total_amount' => $totalAmount,
                    'refunded_amount' => $refundedAmount,
                    'available_amount' => $availableAmount
                ]);
            }

            // استخدام المبلغ المتاح أو المبلغ المحسوب
            $refundAmount = $availableAmount > 0 ? $availableAmount : (int) round(($paymentLog->amount + $paymentLog->vat_amount) * 100);

            $refundData = [
                'amount' => $refundAmount,
            ];

            // تسجيل المبلغ المرسل
            Log::info('Maysar Refund Amount Calculation', [
                'payment_log_amount' => $paymentLog->amount,
                'payment_log_vat' => $paymentLog->vat_amount,
                'calculated_amount_halala' => $refundAmount,
                'calculated_amount_riyal' => $refundAmount / 100,
                'using_available_amount' => $availableAmount > 0
            ]);

            // call the maysar refund API
            $response = $this->callMaysarRefundAPI($paymentLog->transaction_id, $refundData);

            if ($response['success']) {
                // update the payment log status
                $paymentLog->update([
                    'status' => 'refunded',
                    'refund_reference' => $response['refund_reference'],
                    'refund_response' => $response,
                    'refund_notes' => 'Refund processed successfully'
                ]);

                return [
                    'success' => true,
                    'refund_reference' => $response['refund_reference'],
                    'message' => 'Refund processed successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response['error'],
                    'message' => 'Refund failed'
                ];
            }

        } catch (\Exception $e) {
            Log::error('Maysar refund error', [
                'payment_log_id' => $paymentLog->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Refund processing error'
            ];
        }
    }

    /**
     * get payment details from maysar to check available amount
     */
    private function getPaymentDetails(string $paymentId): array
    {
        try {
            $client = new \GuzzleHttp\Client();
            $apiKey = config('maysar.api_key');

            $url = config('maysar.base_url') . '/v1/payments/' . $paymentId;

            $response = $client->get($url, [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($apiKey . ':' . $apiKey),
                    'Content-Type' => 'application/json'
                ],
                'timeout' => config('maysar.timeout', 30)
            ]);

            return json_decode($response->getBody(), true);

        } catch (\Exception $e) {
            Log::error('Failed to get payment details from Maysar', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * call the maysar refund API
     * POST /payments/:id/refund
     */
    private function callMaysarRefundAPI(string $paymentId, array $data): array
    {
        try {
            $client = new \GuzzleHttp\Client();

            // التحقق من وجود API Key
            $apiKey = config('maysar.api_key');
            if (!$apiKey) {
                return [
                    'success' => false,
                    'error' => 'Maysar API key not configured'
                ];
            }

            // استخدام API ميسر الفعلي
            $url = config('maysar.base_url') . '/v1/payments/' . $paymentId . '/refund';

            // تسجيل تفاصيل API Call
            Log::info('Maysar API Call Details', [
                'url' => $url,
                'payment_id' => $paymentId,
                'amount_halala' => $data['amount'],
                'amount_riyal' => $data['amount'] / 100,
                'api_key_prefix' => substr($apiKey, 0, 10) . '...'
            ]);

            $response = $client->post($url, [
                'json' => $data,
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($apiKey . ':' . $apiKey),
                    'Content-Type' => 'application/json'
                ],
                'timeout' => config('maysar.timeout', 30)
            ]);

            $responseData = json_decode($response->getBody(), true);

            return [
                'success' => true,
                'refund_reference' => $responseData['id'] ?? 'REF-' . now()->format('YmdHis'),
                'response' => $responseData
            ];

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBody = $response->getBody()->getContents();
            $statusCode = $response->getStatusCode();

            Log::error('Maysar API Client Error', [
                'status_code' => $statusCode,
                'response_body' => $responseBody,
                'url' => $url,
                'payment_id' => $paymentId,
                'api_key_prefix' => substr($apiKey, 0, 10) . '...'
            ]);

            // معالجة خاصة لخطأ 401
            if ($statusCode === 401) {
                Log::error('Maysar API Authentication Failed', [
                    'api_key_prefix' => substr($apiKey, 0, 15) . '...',
                    'api_key_length' => strlen($apiKey),
                    'url' => $url,
                    'suggestion' => 'Please check your Secret Key in Maysar dashboard'
                ]);

                return [
                    'success' => false,
                    'error' => 'Authentication failed - Please regenerate your Secret Key in Maysar dashboard'
                ];
            }

            // معالجة خاصة لخطأ 400 - المبلغ الزائد
            if ($statusCode === 400 && strpos($responseBody, 'exceeds outstanding') !== false) {
                Log::warning('Maysar Refund Amount Exceeds Available', [
                    'payment_id' => $paymentId,
                    'requested_amount' => $data['amount'],
                    'response_body' => $responseBody,
                    'suggestion' => 'Check payment status or use partial refund'
                ]);

                return [
                    'success' => false,
                    'error' => 'Refund amount exceeds available balance - Payment may be partially refunded already'
                ];
            }

            return [
                'success' => false,
                'error' => 'Maysar API Error: ' . $statusCode . ' - ' . $responseBody
            ];
        } catch (\Exception $e) {
            Log::error('Maysar API General Error', [
                'error' => $e->getMessage(),
                'url' => $url,
                'payment_id' => $paymentId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
