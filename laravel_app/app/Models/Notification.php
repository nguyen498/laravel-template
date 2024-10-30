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
    ];

    protected $casts = [
        'send_date' => 'datetime',
        'data' => 'array',
        'type' => 'integer',
        'status' => 'integer',
        'send_type' => 'integer',
        'send_status' => 'integer',
        'attempts' => 'integer',
    ];

    protected $searchable = [
        'columns' => [
            'notifications.title' => 10,
            'notifications.content' => 10,
        ],
    ];

    public function searchText($term)
    {
        return self::search($term);
    }
}
