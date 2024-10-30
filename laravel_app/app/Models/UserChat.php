<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nicolaslopezj\Searchable\SearchableTrait;

class UserChat extends Model
{
    use HasFactory, SoftDeletes, SearchableTrait, UuidTrait;

    protected $table = 'user_chats';
    public $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'actor_id',
        'actor_name',
        'message',
        'user_group_chat_id',
        'expire_date',
        'medias',
        'type',
        'is_read',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'expire_date' => 'datetime',
        'is_read' => 'boolean',
    ];

    protected $searchable = [
        'column' => [
            'user_chats.message' => 10,
        ]
    ];

    public function searchText($term)
    {
        return self::search($term);
    }

    public function userGroupChat(){
        return $this->belongsTo(UserGroupChat::class);
    }
}
