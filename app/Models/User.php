<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
// use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'address',
        'lat',
        'lng',
        'deviceToken',
        'remember_token',
        'status',
        'updated_at',
        'deleted_at',
        'otp',
        'gallery'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'phone_verified_at' => 'datetime',
        'gallery' => 'array',
        'leave_at_the_door' => 'boolean',
        'hand_over_directly' => 'boolean',
        'call_upon_arrival' => 'boolean',
        'dont_call' => 'boolean',
        'dont_ring_the_doorbell' => 'boolean',
        'meta' => 'array',
    ];

    protected $appends = ['isGuest', 'gallery_urls'];

    protected function picture(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? asset('storage/uploads') . '/' . $value : asset('storage/uploads/blank.png'),
            set: fn($value) => $value,
        );
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => trim(($attributes['first_name'] ?? '') . ' ' . ($attributes['last_name'] ?? '')),
            set: fn($value) => $value,
        );
    }

    public function address()
    {
        return $this->hasOne(UserAddress::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // wallet
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    // referrals
    public static function boot()
    {
        parent::boot();
        static::creating(function ($user) {
            $user->referral_code = strtoupper(Str::random(8));
        });
    }
    //View user subscriptions
    public function userPackages()
    {
        return $this->hasMany(UserPackage::class);
    }

    // Deduct From Active Package
    public function deductFromActivePackage($amount, $orderId = null): bool
    {
        $activePackage = $this->userPackages()
            ->where('is_active', true)
            ->where('end_date', '>=', now())
            ->latest('start_date')
            ->first();

        if (!$activePackage || $activePackage->remaining_credit < $amount) {
            return false; // لا توجد باقة أو الرصيد لا يكفي
        }

        // خصم الرصيد
        $activePackage->decrement('remaining_credit', $amount);

        // تسجيل العملية
        $activePackage->transactions()->create([
            'type' => 'debit',
            'amount' => $amount,
            'description' => 'خصم مقابل طلب',
            'related_order_id' => $orderId,
        ]);

        return true;
    }

    // vouchers
    public function userVouchers()
    {
        return $this->hasMany(UserVoucher::class);
    }

    public function getIsGuestAttribute()
    {
        return str($this->deviceToken)->startsWith('quest_') || $this->first_name === 'Guest';
    }

    public function getGalleryUrlsAttribute()
    {
        if (!$this->gallery) {
            return [];
        }
        return array_map(function ($image) {
            return asset('storage/uploads') . '/' . $image;
        }, $this->gallery);
    }
}
