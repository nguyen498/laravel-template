<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nicolaslopezj\Searchable\SearchableTrait;

class UserInbox extends Model
{
    use HasFactory, SoftDeletes, SearchableTrait, UuidTrait;

    protected $table = 'user_inboxes';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'title',
        'content',
        'data',
        'type',
        'status',
        'inbox_id',
        'inbox_type',
        'attempt',
    ];

    protected function casts(): array
    {
        return [
            'user_id'       => 'integer',
            'title'         => 'string',
            'content'       => 'string',
            'data'          => 'string',
            'type'          => 'integer',
            'status'        => 'integer',
            'inbox_id'      => 'string',
            'inbox_type'    => 'string',
            'attempt'       => 'integer',
        ];
    }

    protected $searchable = [
        'columns' => [
            'user_inboxes.title' => 10,
            'user_inboxes.content' => 10,
        ],
    ];

    public function searchText($term)
    {
        return self::search($term);
    }
}
