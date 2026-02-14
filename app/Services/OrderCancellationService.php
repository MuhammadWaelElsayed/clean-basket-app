<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\Wallet;
use App\Models\UserPackage;
use App\Models\PaymentLog;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderCancellationService
{
    /**
     * process order cancellation and refund the amount
     */
    public function processOrderCancellation(Order $order, string $cancelledBy = 'admin'): array
    {
        try {
            $user = $order->user;

            // check if the user has a wallet
            $wallet = $user->wallet;
            if (!$wallet) {
                $wallet = Wallet::create(['user_id' => $user->id, 'balance' => 0]);
            }

            // check if there are any payments from the wallet and refund the amount
            $walletTransactions = $order->walletTransactions()
                ->where('source', 'wallet')
                ->where('type', 'debit')
                ->get();

            // check if there are any payments from the package and refund the amount
            $packageTransactions = $order->walletTransactions()
                ->where('source', 'package')
                ->where('type', 'debit')
                ->get();

            $totalRefundAmount = 0;
            $refundedTransactions = [];
            $packageRefunded = false;
            $maysarRefunded = false;
            $totalMaysarAmount = 0;
            $maysarRefundResults = [];

            // process refunding the amount from the wallet
            if ($walletTransactions->count() > 0) {
                try {
                    // refund the amount paid from the wallet
                    foreach ($walletTransactions as $transaction) {
                        $refundAmount = $transaction->amount + $transaction->vat_amount;
                        $totalRefundAmount += $refundAmount;

                        // check if the amount is valid before adding it to the wallet
                        if ($refundAmount > 0) {
                            // add the amount to the wallet
                            $wallet->increment('balance', $refundAmount);

                            // log the refund transaction
                            $refundTransaction = $wallet->transactions()->create([
                                'type' => 'credit',
                                'amount' => $transaction->amount,
                                'vat_amount' => $transaction->vat_amount,
                                'source' => 'wallet',
                                'description' => $this->getRefundDescription($order, $cancelledBy, 'wallet'),
                                'related_order_id' => $order->id,
                                'transaction_id' => $this->generateTransactionId($cancelledBy, 'wallet'),
                            ]);

                            $refundedTransactions[] = $refundTransaction;
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Error refunding wallet amount for order ' . $order->id . ': ' . $e->getMessage());
                }
            }

            // process refunding the amount from the package
            if ($packageTransactions->count() > 0) {
                try {
                    foreach ($packageTransactions as $transaction) {
                        if ($transaction->user_package_id) {
                            $userPackage = UserPackage::find($transaction->user_package_id);
                            if ($userPackage) {
                                $refundAmount = $transaction->amount + $transaction->vat_amount;
                                $userPackage->increment('remaining_credit', $refundAmount);
                                $packageRefunded = true;

                                // log the refund transaction
                                $refundTransaction = $wallet->transactions()->create([
                                    'type' => 'credit',
                                    'amount' => $transaction->amount,
                                    'vat_amount' => $transaction->vat_amount,
                                    'source' => 'package',
                                    'description' => $this->getRefundDescription($order, $cancelledBy, 'package'),
                                    'related_order_id' => $order->id,
                                    'user_package_id' => $userPackage->id,
                                    'transaction_id' => $this->generateTransactionId($cancelledBy, 'package'),
                                ]);

                                $refundedTransactions[] = $refundTransaction;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Error refunding package credit for order ' . $order->id . ': ' . $e->getMessage());
                }
            }

            // process refunding the amount from maysar
            $maysarTransactions = PaymentLog::where('order_id', $order->id)
                ->whereIn('payment_method', ['maysar', 'maysar_partial'])
                ->where('status', 'paid')
                ->get();

            if ($maysarTransactions->count() > 0) {
                $maysarService = new MaysarService();

                foreach ($maysarTransactions as $paymentLog) {
                    $maysarAmount = $paymentLog->amount + $paymentLog->vat_amount;
                    $totalMaysarAmount += $maysarAmount;

                    // تسجيل تفاصيل المعاملة
                    Log::info('Processing Maysar refund', [
                        'order_id' => $order->id,
                        'payment_log_id' => $paymentLog->id,
                        'payment_method' => $paymentLog->payment_method,
                        'amount' => $maysarAmount,
                        'transaction_id' => $paymentLog->transaction_id
                    ]);

                    // محاولة الاسترجاع من ميسر
                    $refundResult = $maysarService->refundPayment($paymentLog);

                    if ($refundResult['success']) {
                        $maysarRefunded = true;
                        $maysarRefundResults[] = $refundResult;
                    } else {
                        // في حالة فشل الاسترجاع، تسجيل في اللوج
                        Log::warning('Maysar refund failed - Manual processing required', [
                            'order_id' => $order->id,
                            'payment_log_id' => $paymentLog->id,
                            'amount' => $maysarAmount,
                            'transaction_id' => $paymentLog->transaction_id,
                            'error' => $refundResult['error']
                        ]);

                        // يمكن إضافة إشعار للإدارة هنا
                        // أو حفظ في جدول منفصل للطلبات اليدوية
                    }
                }
            }

            // prepare the notification data
            $notificationData = $this->prepareNotificationData($order, $user, $cancelledBy, $totalRefundAmount, $packageRefunded, $maysarRefunded, $totalMaysarAmount);

            // send the notifications
            Controller::sendNotifications($notificationData, 'user');

            // log the refund information
            Log::info('Order cancellation refund processed', [
                'order_id' => $order->id,
                'cancelled_by' => $cancelledBy,
                'total_refund_amount' => $totalRefundAmount,
                'package_refunded' => $packageRefunded,
                'refunded_transactions_count' => count($refundedTransactions)
            ]);

            return [
                'success' => true,
                'total_refund_amount' => $totalRefundAmount,
                'package_refunded' => $packageRefunded,
                'maysar_refunded' => $maysarRefunded,
                'total_maysar_amount' => $totalMaysarAmount,
                'maysar_refund_results' => $maysarRefundResults,
                'refunded_transactions' => $refundedTransactions,
                'notification_data' => $notificationData
            ];
        } catch (\Exception $e) {
            Log::error('Error processing order cancellation refund', [
                'order_id' => $order->id,
                'cancelled_by' => $cancelledBy,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * prepare the notification data
     */
    private function prepareNotificationData(Order $order, User $user, string $cancelledBy, float $totalRefundAmount, bool $packageRefunded, bool $maysarRefunded = false, float $totalMaysarAmount = 0): array
    {
        $isAdminCancellation = $cancelledBy === 'admin';

        $notificationData = [
            "title" => $isAdminCancellation
                ? 'Your Order #' . $order->order_code . ' is Cancelled by Admin'
                : 'Your order has been cancelled',
            "title_ar" => $isAdminCancellation
                ? 'تم إلغاء طلبك رقم #' . $order->order_code . ' من قبل الإدارة'
                : 'تم إلغاء طلبك',
            "message" => $isAdminCancellation
                ? 'Your Order #' . $order->order_code . ' is Cancelled by Admin'
                : 'You have cancelled your order #' . $order->order_code . '.',
            "message_ar" => $isAdminCancellation
                ? 'تم إلغاء طلبك رقم #' . $order->order_code . ' من قبل الإدارة'
                : 'لقد تم إلغاء طلبك رقم #' . $order->order_code . '. سنوافيك بالتحديثات قريبًا.',
            "user" => $user
        ];

        // add a refund message if the amount has been refunded
        if ($totalRefundAmount > 0 || $packageRefunded || $maysarRefunded || $totalMaysarAmount > 0) {
            $refundMessage = "";
            $refundMessageAr = "";

            if ($totalRefundAmount > 0) {
                $refundMessage .= " Amount {$totalRefundAmount} SAR has been refunded to your wallet.";
                $refundMessageAr .= " تم إرجاع مبلغ {$totalRefundAmount} ريال إلى محفظتك.";
            }

            if ($packageRefunded) {
                $refundMessage .= " Package credit has been restored.";
                $refundMessageAr .= " تم استرجاع رصيد الباقة.";
            }

            if ($maysarRefunded) {
                $refundMessage .= " Maysar payment has been refunded to your account.";
                $refundMessageAr .= " تم استرجاع الدفع من ميسر إلى حسابك.";
            } else if ($totalMaysarAmount > 0) {
                $refundMessage .= " Maysar refund request has been created.";
                $refundMessageAr .= " تم إنشاء طلب استرجاع الدفع من ميسر.";
            }

            $notificationData["message"] .= $refundMessage;
            $notificationData["message_ar"] .= $refundMessageAr;
        }

        return $notificationData;
    }

    /**
     * create the refund description
     */
    private function getRefundDescription(Order $order, string $cancelledBy, string $source): string
    {
        $prefix = $cancelledBy === 'admin' ? 'Admin' : 'Customer';
        $sourceText = $source === 'wallet' ? 'wallet' : 'package credit';

        return "{$prefix} refund for cancelled order #{$order->order_code}";
    }

    /**
     * generate a unique transaction id
     */
    private function generateTransactionId(string $cancelledBy, string $source): string
    {
        $prefix = $cancelledBy === 'admin' ? 'ADM-REF' : 'REF';
        $sourcePrefix = $source === 'package' ? '-PKG' : '';

        return $prefix . $sourcePrefix . '-' . now()->format('YmdHis') . '-' . Str::random(4);
    }

    /**
     * prepare the success message for the admin
     */
    public function prepareAdminSuccessMessage(float $totalRefundAmount, bool $packageRefunded): string
    {
        $successMessage = 'Order CANCELLED Successfully!';

        if ($totalRefundAmount > 0 || $packageRefunded) {
            $successMessage .= " ";

            if ($totalRefundAmount > 0) {
                $successMessage .= "Amount {$totalRefundAmount} SAR has been refunded to customer's wallet.";
            }

            if ($packageRefunded) {
                $successMessage .= "Package credit has been restored.";
            }
        }

        return $successMessage;
    }

    /**
     * prepare the success message for the customer
     */
    public function prepareCustomerSuccessMessage(float $totalRefundAmount, bool $packageRefunded): string
    {
        $successMessage = "Order Cancelled successfully!";

        if ($totalRefundAmount > 0 || $packageRefunded) {
            $successMessage .= " ";

            if ($totalRefundAmount > 0) {
                $successMessage .= "Amount {$totalRefundAmount} SAR has been refunded to your wallet.";
            }

            if ($packageRefunded) {
                $successMessage .= "Package credit has been restored.";
            }
        }

        return $successMessage;
    }
}
