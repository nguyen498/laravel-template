<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

class State extends Model
{
    use HasFactory, SearchableTrait;

    protected $table = 'states';

    protected $fillable = [
        'country_id',
        'name',
        'code',
        'sort',
        'status',
    ];

    /**
     * Casts for specific attributes.
     */
    protected $casts = [
        'sort' => 'integer',
        'status' => 'integer',
    ];

    protected $searchable = [
        'column' => [
            'states.name' => 10
        ]
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
