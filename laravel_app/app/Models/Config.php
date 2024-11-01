<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    use HasFactory, UuidTrait;
    protected $table = 'configs';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'value',
        'data',
        'type',
        'status',
        'medias',
        'slug',
    ];

    protected $casts = [
        'id' => 'string',
        'name' => 'string',
        'value' => 'string',
        'data' => 'string',
        'type' => 'integer',
        'status' => 'integer',
        'medias' => 'string',
        'slug' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
