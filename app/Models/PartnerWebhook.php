<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerWebhook extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'source_secret',
    ];
    /**
     * Get all logs for this webhook.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(WebhookLog::class);
    }

    /**
     * Get recent successful logs.
     */
    public function recentSuccessLogs(int $limit = 10)
    {
        return $this->logs()
            ->where('status', 'success')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent failed logs.
     */
    public function recentFailedLogs(int $limit = 10)
    {
        return $this->logs()
            ->whereIn('status', ['failed', 'permanently_failed'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get success rate percentage.
     */
    public function getSuccessRateAttribute(): float
    {
        $total = $this->logs()->count();

        if ($total === 0) {
            return 0;
        }

        $successful = $this->logs()->where('status', 'success')->count();

        return round(($successful / $total) * 100, 2);
    }

    /**
     * Get average response time in milliseconds.
     */
    public function getAverageResponseTimeAttribute(): ?float
    {
        return $this->logs()
            ->where('status', 'success')
            ->whereNotNull('duration_ms')
            ->avg('duration_ms');
    }
}
