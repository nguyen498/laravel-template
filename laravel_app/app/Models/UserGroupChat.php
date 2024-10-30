<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nicolaslopezj\Searchable\SearchableTrait;

class UserGroupChat extends Model
{
    use HasFactory, SoftDeletes, UuidTrait, SearchableTrait;

    protected $table = 'user_group_chats';
    public $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

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

    protected $searchable = [
        'column' => [
            'user_group_chats.title' => 10,
        ]
    ];

    public function searchText($term)
    {
        return self::search($term);
    }

    public function userChat(){
        return $this->hasMany(UserChat::class);
    }
}
