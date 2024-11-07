<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserGroupChatStatus extends Model
{
    use HasFactory, SoftDeletes;

    //status
    const STATUS_UNACTIVE     = 0;
    const STATUS_ACTIVE       = 1;


    protected $table = 'user_group_chat_status';
    public $primaryKey = 'id';

    protected $fillable = [
        'id',
        'user_group_chat_id',
        'user_id',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
