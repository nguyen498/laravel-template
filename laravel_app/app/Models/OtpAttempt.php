<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

class OtpAttempt extends Model
{
    use HasFactory;

    protected $table = 'otp_attempts';

    protected $primaryKey = 'id';

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
}
