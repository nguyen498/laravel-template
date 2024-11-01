<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostAdvertising extends Model
{
    use HasFactory, UuidTrait;

    protected $table = 'post_advertisings';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'post_id',
        'expire_date',
        'count_click',
        'count_comment',
        'count_like',
    ];

    protected $casts = [
        'id' => 'string',
        'user_id' => 'string',
        'post_id' => 'string',
        'expire_date' => 'datetime', // tự động chuyển đổi sang đối tượng Carbon
        'count_click' => 'integer', // số lượng click
        'count_comment' => 'integer', // số lượng comment
        'count_like' => 'integer', // số lượng like
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
