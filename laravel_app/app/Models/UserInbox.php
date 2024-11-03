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

    // type
    const TYPE_PASSENGER_CONFIRM_TRIP       = 1;
    const TYPE_PASSENGER_ARRIVED_TRIP       = 2;
    const TYPE_PASSENGER_FINISHED_TRIP      = 3;
    const TYPE_PASSENGER_INSURANCE_TRIP     = 4;
    // driver
    const TYPE_DRIVER_ACTIVE_ACCOUNT_STATUS         = 10; // by admin
    const TYPE_DRIVER_DEACTIVE_ACCOUNT_STATUS       = 11; // by admin
    const TYPE_DRIVER_NEED_DEPOSIT          = 12;
    const TYPE_DRIVER_NOT_ENOUGH_DOCUMENT   = 13;
    const TYPE_DRIVER_DEACTIVE_IS_RECEIVE_RIDE_DOCUMENT   = 14;
    const TYPE_DRIVER_USE_TRIP              = 15;
    const TYPE_DRIVER_DEACTIVE_RETRY        = 16;

    const TYPE_NOTIFICATION                 = 20;
    const TYPE_CHAT_ROOM                    = 100;
    const TYPE_TRIP_CHAT                    = 101;
    const TYPE_SUPPORT_CHAT                 = 102;
    // status
    const STATUS_NEW            = 1;
    const STATUS_SEND           = 2;
    const STATUS_SEND_FAILED    = 3;
    // comment type
    const COMMENT_TYPE_WITHOUT_COMMENT      = 1;
    const COMMENT_TYPE_COMMENT              = 2;
    // read status
    const READ_STATUS_NOT_READ              = 1;
    const READ_STATUS_READ                  = 2;

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
