<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestDriveRequest extends Model
{
    protected $fillable = [
        'user_id',
        'address',
        'phone',
        'desired_date',
        'desired_time',
        'license_series',
        'license_number',
        'license_issue_date',
        'car_brand',
        'car_model',
        'payment_type',
        'is_agreed',
        'status',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'desired_date' => 'date',
            'desired_time' => 'string',
            'license_issue_date' => 'date',
            'is_agreed' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}