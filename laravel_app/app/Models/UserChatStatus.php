<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserChatStatus extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_chat_status';
    public $primaryKey = 'id';

    protected $fillable = [
        'id',
        'user_group_chat_id',
        'user_id',
        'message_id',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
