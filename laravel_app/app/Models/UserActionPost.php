<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActionPost extends Model
{
    use HasFactory, UuidTrait;
    protected $table = 'user_action_posts';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'post_id',
        'category_id',
        'count',
    ];

    protected $casts = [
        'id' => 'string',
        'user_id' => 'string',
        'post_id' => 'string',
        'category_id' => 'string',
        'count' => 'integer', // kiểu số nguyên
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function post(){
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function category(){
        return $this->belongsTo(Category::class, 'category_id');
    }
}
