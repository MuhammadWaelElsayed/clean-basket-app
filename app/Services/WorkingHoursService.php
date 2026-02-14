<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WorkingHoursService
{
    /**
     * اجلب فترات العمل لليوم (قد تكون أكثر من فترة) مثل:
     * [['open' => '09:00:00', 'close' => '13:00:00'], ['open' => '16:00:00', 'close' => '22:00:00']]
     */
    public function getDailyIntervals(int $vendorId, Carbon $date): array
    {
        $dow = (int) $date->dayOfWeek; // 0..6

        return DB::table('vendor_working_hours')
            ->where('vendor_id', $vendorId)
            ->where('day_of_week', $dow)
            ->where('is_closed', 0)
            ->orderBy('open_time')
            ->get(['open_time', 'close_time'])
            ->map(fn($r) => ['open' => $r->open_time, 'close' => $r->close_time])
            ->toArray();
    }

    /**
     * توليد فتحات زمنية لمدة ساعة داخل فترات العمل ليوم معين.
     * - $from: لا نولّد أي فتحة تبدأ قبل هذا الزمن (يُستخدم لتجاهل الماضي/التأكد من 20 ساعة للتسليم).
     * - $slotMinutes: طول الفتحة بالدقائق (افتراضي 60).
     */
    public function generateDaySlots(
        int $vendorId,
        Carbon $date,
        ?Carbon $from = null,
        int $slotMinutes = 60,
        int $offsetAfterOpenMinutes = 60 // ← فتحة تبدأ بعد X دقيقة من وقت الفتح
    ): array {
        $from = $from?->copy() ?? $date->copy()->startOfDay();
        $intervals = $this->getDailyIntervals($vendorId, $date);

        $slots = [];
        foreach ($intervals as $iv) {
            // بداية الفترة = وقت الفتح + الأوفست
            $start = Carbon::parse($date->toDateString() . ' ' . $iv['open'])
                ->addMinutes($offsetAfterOpenMinutes);

            $end   = Carbon::parse($date->toDateString() . ' ' . $iv['close']);

            // لا نعرض فتحة تبدأ قبل "from" (لتجاهل الماضي في نفس اليوم)
            if ($start->lessThan($from)) {
                $start = $from->copy();
            }

            // محاذاة لبداية الفتحة حسب طولها (00/30/60…)
            $minuteStep = $slotMinutes;
            $start->minute = intdiv($start->minute, $minuteStep) * $minuteStep;
            $start->second = 0;
            if ($start->lt($from)) {
                $start->addMinutes($minuteStep);
            }

            // توليد الفتحات: آخر فتحة يجب أن تنتهي ≤ وقت الإغلاق
            while ($start->copy()->addMinutes($slotMinutes)->lte($end)) {
                $slotStart = $start->copy();
                $slotEnd   = $start->copy()->addMinutes($slotMinutes);

                $slots[] = [
                    'date'      => $slotStart->toDateString(),
                    'start'     => $slotStart->format('H:i'),
                    'end'       => $slotEnd->format('H:i'),
                    'start_iso' => $slotStart->toIso8601String(),
                    'end_iso'   => $slotEnd->toIso8601String(),
                ];

                $start->addMinutes($minuteStep);
            }
        }

        return $slots;
    }

    /**
     * أعطني فتحات استلام (Pickup) ليوم معيّن، مع استبعاد الماضي اليوم.
     * - $daysHorizon: كم يوم نغطي عند عدم وجود فتحات كافية اليوم (مثلاً جرّب اليوم + الغد).
     * - $minCount: حد أدنى لعدد الفتحات لإرجاعها (اختياري).
     */
    public function listPickupSlots(
        int $vendorId,
        ?string $dateYmd = null,
        int $daysHorizon = 2,
        int $minCount = 6,
        int $slotMinutes = 60
    ): array {
        $now = now();
        $date = $dateYmd ? Carbon::parse($dateYmd) : $now->copy();

        $results = [];
        for ($i = 0; $i < $daysHorizon; $i++) {
            $d = $date->copy()->addDays($i);

            // إذا اليوم الحالي: لا نسمح بالماضي → from = max(now, opening)
            $from = $d->isSameDay($now) ? $now : $d->copy()->startOfDay();

            $daySlots = $this->generateDaySlots($vendorId, $d, $from, $slotMinutes);
            if (!empty($daySlots)) {
                $results = array_merge($results, $daySlots);
            }
            if (count($results) >= $minCount) break;
        }
        return $results;
    }

    /**
     * فتحات تسليم (Dropoff) تبدأ بعد pickup_at + $gapHours.
     * - نجرّب عدة أيام للأمام حتى نجد فتحات كافية.
     */
    public function listDeliverySlots(
        int $vendorId,
        Carbon $pickupAt,
        int $gapHours = 20,
        int $daysHorizon = 5,
        int $minCount = 6,
        int $slotMinutes = 60
    ): array {
        $earliest = $pickupAt->copy()->addHours($gapHours);

        $results = [];
        for ($i = 0; $i < $daysHorizon; $i++) {
            $d = $earliest->copy()->addDays($i)->startOfDay();
            // أول يوم: from = earliest، ثم الأيام التالية from = بداية اليوم
            $from = $i === 0 ? $earliest : $d;

            $daySlots = $this->generateDaySlots($vendorId, $d, $from, $slotMinutes);
            if (!empty($daySlots)) {
                $results = array_merge($results, $daySlots);
            }
            if (count($results) >= $minCount) break;
        }
        return $results;
    }
}
