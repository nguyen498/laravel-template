<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nicolaslopezj\Searchable\SearchableTrait;

class Address extends Model
{
    use HasFactory, SoftDeletes, SearchableTrait,UuidTrait;

    protected $table = 'addresses';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'address',
        'full_address',
        'country_id',
        'country_name',
        'state_id',
        'state_name',
        'postcode',
        'type',
        'status',
        'addressable_id',
        'addressable_type',
        'lng',
        'lat',
    ];

    /**
     * Casts for specific attributes.
     */
    protected $casts = [
        'type' => 'integer',
        'status' => 'integer',
        'lng' => 'float',
        'lat' => 'float',
    ];

    protected $searhable = [
        'columns' => [
            'addresses.full_address' => 10,
        ]
    ];

    /**
     * Get the parent addressable model (if polymorphic).
     */
    public function addressable()
    {
        return $this->morphTo();
    }
}
