<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

class City extends Model
{
    use HasFactory, SearchableTrait;

    protected $table = 'cities';

    protected $fillable = [
        'name',
        'state_id',
    ];

    /**
     * Casts for specific attributes.
     */
    protected $casts = [
    ];

    protected $hidden = [
        "created_at",
        "updated_at",
    ];

    protected $searchable = [
        'column' => [
            'cities.name' => 10
        ]
    ];

    public function searchText($term)
    {
        return self::search($term);
    }
}
