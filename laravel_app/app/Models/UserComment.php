<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

class UserComment extends Model
{
    use HasFactory, SearchableTrait, UuidTrait;
    protected $table = 'user_comments';
    protected $keyType = 'string';
    public $incrementing = false;

    // type
    const TYPE_USER         = 1;
    const TYPE_SYSTEM       = 2;
    // is read
    const NOT_READ          = 0;
    const IS_READ           = 1;

    protected $fillable = [
        'id',
        'parent_id',
        'actor_id',
        'actor_type',
        'actor_name',
        'actor_logo',
        'message',
        'verb',
        'object_id',
        'object_type',
        'expire_date',
        'medias',
        'type',
        'is_read',
    ];

    protected $casts = [
        'id' => 'string',
        'parent_id' => 'string',
        'actor_id' => 'string',
        'actor_type' => 'string',
        'actor_name' => 'string',
        'actor_logo' => 'string',
        'message' => 'string', // nếu bạn muốn lưu dưới dạng string; nếu bạn muốn lưu là longtext, có thể bỏ qua
        'verb' => 'string',
        'object_id' => 'string',
        'object_type' => 'string',
        'expire_date' => 'datetime', // để tự động chuyển đổi sang Carbon
        'medias' => 'array', // nếu lưu dưới dạng JSON, bạn có thể chuyển đổi thành mảng
        'type' => 'integer',
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $searchable = [
        'column' => [
            'user_comments.message' => 10
        ]
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'object_id');
    }

    public function parentComment()
    {
        return $this->belongsTo(UserComment::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(UserComment::class, 'parent_id', 'id')->with('children');
    }

}
