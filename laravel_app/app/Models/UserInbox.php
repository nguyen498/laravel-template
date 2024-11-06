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


    //type
    const TYPE_COMMENT_TO_POST_OWNER        = 1;
    const TYPE_COMMENT_TO_PARENT_COMMENT    = 2;

    // status
    const STATUS_NEW            = 1;
    const STATUS_SEND           = 2;
    const STATUS_SEND_FAILED    = 3;
    // comment type
    const COMMENT_TYPE_WITHOUT_COMMENT      = 1;
    const COMMENT_TYPE_COMMENT              = 2;
    // read status
    const READ_STATUS_NOT_READ              = 0;
    const READ_STATUS_READ                  = 1;

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
