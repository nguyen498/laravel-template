<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserMedia extends Model
{
    use HasFactory, SoftDeletes, UuidTrait;

    protected $table = 'user_medias';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'name',
        'path',
        'sort',
        'type',
    ];

    protected function casts()
    {
        return [
            'user_id'       => 'integer',
            'name'         => 'string',
            'path'       => 'string',
            'type'          => 'integer',
            'sort'        => 'integer',
        ];
    }
}
