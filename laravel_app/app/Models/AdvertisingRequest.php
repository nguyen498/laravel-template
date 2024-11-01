<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvertisingRequest extends Model
{
    use HasFactory, UuidTrait;
    protected $table = 'advertising_requests';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'user_name',
        'user_phone',
        'post_id',
        'post_name',
        'start_date',
        'expire_date',
        'notes',
        'adv_type',
        'type',
        'status',
    ];

    protected $casts = [
        'id' => 'string',
        'user_id' => 'string',
        'user_name' => 'string',
        'user_phone' => 'string',
        'post_id' => 'string',
        'post_name' => 'string',
        'start_date' => 'datetime',
        'expire_date' => 'datetime',
        'notes' => 'string',
        'adv_type' => 'integer',
        'type' => 'integer',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
