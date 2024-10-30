<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $table = 'devices';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'push_token',
        'platform',
        'device_info',
        'deviceable_id',
        'deviceable_type',
    ];

    protected function casts(): array
    {
        return [
            'push_token'        => 'string',
            'platform'          => 'string',
            'device_info'       => 'string',
            'deviceable_id'     => 'string',
            'deviceable_type'   => 'string',
        ];
    }

    /**
     * Get the parent deviceable model (user or admin, etc.).
     */
    public function deviceable()
    {
        return $this->morphTo();
    }
}
