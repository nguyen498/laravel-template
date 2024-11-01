<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPost extends Model
{
    use HasFactory, UuidTrait;
    protected $table = 'user_posts';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'post_id',
        'type',
        'is_notification',
    ];

    protected $casts = [
        'id' => 'string',
        'user_id' => 'string',
        'post_id' => 'string',
        'type' => 'integer', // kiểu số nguyên
        'is_notification' => 'boolean', // kiểu boolean
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
