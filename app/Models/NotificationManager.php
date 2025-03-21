<?php

namespace App\Models;

use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationManager extends Model
{
    protected $table = 'notifications_manager';

    protected $fillable = [
        'title',
        'body',
        'notification_type',
        'data',
        'sent_by',
        'filters',
        'schedule_at',
        'sent_at',
        'total_recipients',
        'successful_sent',
        'failed_sent',
        'read_count',
        'click_count',
        'status',
        'error_log',
    ];

    protected $casts = [
        'data' => 'array',
        'filters' => 'array',
        'schedule_at' => 'datetime',
        'sent_at' => 'datetime',
        'status' => NotificationStatus::class,
        'notification_type' => NotificationType::class,
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function incrementReadCount(): void
    {
        $this->increment('read_count');
    }

    public function incrementClickCount(): void
    {
        $this->increment('click_count');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
