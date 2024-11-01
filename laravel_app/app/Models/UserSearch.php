<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSearch extends Model
{
    use HasFactory, UuidTrait;
    protected $table = 'user_searches';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'keyword',
        'location',
        'data_search',
        'data_raw',
    ];

    protected $casts = [
        'id' => 'string',
        'user_id' => 'string',
        'keyword' => 'string',
        'location' => 'string', // có thể dùng 'json' nếu cần
        'data_search' => 'string',
        'data_raw' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
