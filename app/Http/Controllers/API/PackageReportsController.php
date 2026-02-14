<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PackageTransaction;
use App\Models\UserPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PackageReportsController extends Controller
{

        /**
         * Financial report: total subscriptions value, packages sold, consumed amount, cashback given.
         */
        public function financial(): \Illuminate\Http\JsonResponse
        {
            // إجمالي قيمة الاشتراكات
            $totalSubscriptions = UserPackage::sum('total_credit');

            // عدد الباقات المبيعة
            $packagesSold = UserPackage::count();

            // المبالغ المستهلكة
            $consumed = UserPackage::select(DB::raw('SUM(total_credit - remaining_credit) as used'))->value('used');

            // إجمالي الكاش باك المطبق
            $cashbackGiven = UserPackage::join('packages', 'user_packages.package_id', '=', 'packages.id')
                ->select(DB::raw('SUM(packages.cashback_amount) as total_cashback'))
                ->value('total_cashback');

            return response()->json([
                'status' => true,
                'data' => [
                    'total_subscriptions_value' => $totalSubscriptions,
                    'packages_sold'             => $packagesSold,
                    'total_consumed_amount'     => $consumed,
                    'total_cashback_given'      => $cashbackGiven,
                ]
            ]);
        }

        /**
         * Usage report: orders per package, average consumption, auto-renew ratio.
         */
        public function usage(): \Illuminate\Http\JsonResponse
        {
            // عدد الطلبات لكل باقة
            $ordersPerPackage = PackageTransaction::where('type', 'debit')
                ->whereNotNull('related_order_id')
                ->join('user_packages', 'package_transactions.user_package_id', '=', 'user_packages.id')
                ->join('packages', 'user_packages.package_id', '=', 'packages.id')
                ->groupBy('packages.name')
                ->select('packages.name', DB::raw('COUNT(*) as orders_count'))
                ->get();

            // متوسط استهلاك الرصيد
            $avgConsumption = UserPackage::select(DB::raw('AVG(total_credit - remaining_credit) as avg_used'))->value('avg_used');

            // نسبة التجديد التلقائي
            $totalPackages = UserPackage::count();
            $autoRenewCount = UserPackage::where('auto_renew', true)->count();
            $autoRenewRatio = $totalPackages ? round($autoRenewCount / $totalPackages * 100, 2) : 0;

            return response()->json([
                'status' => true,
                'data' => [
                    'orders_per_package' => $ordersPerPackage,
                    'average_consumption' => $avgConsumption,
                    'auto_renew_ratio'    => $autoRenewRatio,
                ]
            ]);
        }

        /**
         * Priority report: count of priority package orders.
         */
        public function priority(): \Illuminate\Http\JsonResponse
        {
            $priorityOrders = PackageTransaction::where('type', 'debit')
                ->whereNotNull('related_order_id')
                ->join('user_packages', 'package_transactions.user_package_id', '=', 'user_packages.id')
                ->join('packages', 'user_packages.package_id', '=', 'packages.id')
                ->where('packages.has_priority', true)
                ->count();

            return response()->json([
                'status' => true,
                'data' => [
                    'priority_orders_count' => $priorityOrders,
                ]
            ]);
        }
    }
