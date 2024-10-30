<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserGroupChat extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_group_chats';
    protected $fillable = [
        'id',
        'post_id',
        'title',
        'post_logo',
        'actor_id',
        'actor_name',
        'actor_logo',
        'user_id',
        'user_logo',
        'user_name',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
