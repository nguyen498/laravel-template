<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

class OtpAttempt extends Model
{
    use HasFactory, UuidTrait, SearchableTrait;

    protected $table = 'otp_attempts';
    protected $primaryKey = 'id';
    public $incrementing = false;

    // type
    const REGISTER          = 1;
    const FORGET_PASSWORD   = 2;
    const CHANGE_PHONE      = 3;
    // status
    const NOT_USE   = 0;
    const USE       = 1;
    const DONE      = 2;
    const CANCEL    = 10;

    protected $fillable = [
        'id',
        'phone',
        'name',
        'otp',
        'is_confirm',
        'type',
        'status',
        'valid_in',
        'attempts',
        'device_id',
    ];

    protected function casts(): array
    {
        return [
            'id'            => 'string',
            'phone'         => 'string',
            'name'          => 'string',
            'otp'           => 'string',
            'is_confirm'    => 'boolean',
            'type'          => 'integer',
            'status'        => 'integer',
            'valid_in'      => 'integer',
            'attempts'      => 'integer',
            'device_id'     => 'string',
        ];
    }

    protected $searchable = [
        'columns' => [
            'otp_attemps.phone' => 80,
            'otp_attemps.otp' => 80
        ]
    ];

    public function searchText(string $term)
    {
        return self::search($term);
    }
}
