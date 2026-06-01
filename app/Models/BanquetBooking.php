<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BanquetBooking extends Model
{
    protected $fillable = [
        'user_id',
        'hall_type',
        'banquet_date',
        'start_time',
        'payment_type',
        'status',
        'review_text',
    ];

    protected function casts(): array
    {
        return [
            'banquet_date' => 'date',
            'start_time' => 'string',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}