<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nicolaslopezj\Searchable\SearchableTrait;

class Notification extends Model
{
    use HasFactory, SoftDeletes, SearchableTrait, UuidTrait;

    protected $table = 'notifications';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    //status
    const STATUS_DRAFF          = 0;
    const STATUS_READY          = 1;
    const STATUS_SEND           = 2;
    //type
    const TYPE_ALL              = 1;
    const TYPE_USER             = 2;
    //send_type
    const TYPE_ONCE             = 1;
    const TYPE_EVERYDAY         = 2;
    const TYPE_EVERY_WEEK       = 3;
    const TYPE_EVERY_MONTH      = 4;
    // send
    const SEND_NEW                  = 1;
    const SEND_SUCCESS              = 2;
    const SEND_DESTROY              = 3;

    protected $fillable = [
        'id',
        'reference',
        'title',
        'content',
        'data',
        'type',
        'status',
        'send_type',
        'send_status',
        'send_date',
        'attempts',
        'notification_id',
        'notification_type',
        'created_id',
        'created_name',
        'user_ids',
        'last_run'
    ];

    protected $casts = [
        'send_date' => 'datetime',
        'data' => 'array',
        'user_ids' => 'array',
        'type' => 'integer',
        'status' => 'integer',
        'send_type' => 'integer',
        'send_status' => 'integer',
        'attempts' => 'integer',
    ];

    protected $searchable = [
        'columns' => [
            'notifications.title' => 5,
            'notifications.send_date' => 5,
            'notifications.notificationable_type' => 5
        ]
    ];

    public function searchText($term)
    {
        return self::search($term);
    }
}
