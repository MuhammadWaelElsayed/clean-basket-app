<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Ticket extends Model
{
    protected $fillable = [
        'ticket_number',
        'issue_category_id',
        'sub_issue_category_id',
        'order_code',
        'user_id',
        'opened_at',
        'status',
        'description',
        'note',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            // 1) اضبط opened_at الآن (يعتمد APP_TIMEZONE)
            if (empty($ticket->opened_at)) {
                $ticket->opened_at = now();
            }

            // 2) بناء البادئة حسب اليوم
            $datePart = Carbon::now()->format('mdY');        // MMDDYYYY
            $prefix   = "TK-{$datePart}";
            $lockKey  = "ticket_serial_{$datePart}";

            // 3) خذ قفل اسمي لمنع السباق (timeout = 5 ثوانٍ)
            $gotLock = (bool) collect(DB::select('SELECT GET_LOCK(?, 5) AS l', [$lockKey]))->first()->l;

            if (!$gotLock) {
                // ما قدرنا ناخذ القفل خلال 5 ثواني
                throw new \RuntimeException('Could not acquire ticket number lock.');
            }

            try {
                // 4) اجلب آخر رقم لهذا اليوم مرتّب تنازلياً
                $last = self::whereDate('created_at', Carbon::today())
                    ->where('ticket_number', 'like', "{$prefix}%")
                    ->orderByDesc('ticket_number')
                    ->value('ticket_number');

                // 5) استخرج التسلسل السابق وزِد واحد
                $next = 1;
                if ($last && preg_match('/(\d{4})$/', $last, $m)) {
                    $next = ((int) $m[1]) + 1;
                }

                // 6) صفّر لـ 4 خانات
                $serial = str_pad($next, 4, '0', STR_PAD_LEFT);

                // 7) عيّن الرقم النهائي
                $ticket->ticket_number = "{$prefix}{$serial}";
            } finally {
                // 8) أطلق القفل دائماً
                DB::select('SELECT RELEASE_LOCK(?)', [$lockKey]);
            }
        });
    }
    // protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($ticket) {
    //         // 1. احصل على التاريخ بصيغة MMDDYYYY
    //         $datePart = Carbon::now()->format('mdY');

    //         // 2. احسب عدد التذاكر التي فتحت اليوم (فتح.created_at)
    //         $todayCount = self::whereDate('created_at', Carbon::today())->count() + 1;

    //         // 3. حوّل العدد إلى رقم متسلسل 4 خانات (مثلاً 0001)
    //         $serial = str_pad($todayCount, 4, '0', STR_PAD_LEFT);

    //         // 4. اجمع الصيغة كاملة
    //         $ticket->ticket_number = "TK-{$datePart}{$serial}";

    //         // 5. تأكد أنّ opened_at يعكس الآن
    //         $ticket->opened_at = Carbon::now();
    //     });
    // }

    // app/Models/Ticket.php

    public function getRouteKeyName()
    {
        return 'ticket_number';
    }


    public function category()
    {
        return $this->belongsTo(IssueCategory::class, 'issue_category_id');
    }

    public function subCategory()
    {
        return $this->belongsTo(SubIssueCategory::class, 'sub_issue_category_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_code', 'order_code');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
