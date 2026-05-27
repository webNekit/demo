<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookCard extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'author',
        'title',
        'type',
        'publisher',
        'pub_year',
        'binding',
        'condition',
        'status',
        'rejection_reason',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}