<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

class Country extends Model
{
    use HasFactory, SearchableTrait;

    protected $table = 'countries';

    protected $fillable = [
        'name',
        'phone_code',
        'code',
        'sort',
        'status'
    ];

    /**
     * Casts for specific attributes.
     */
    protected $casts = [
        'sort' => 'integer',
        'status' => 'integer'
    ];

    protected $hidden = [
        "status",
        "created_at",
        "updated_at",
    ];

    protected $searchable = [
        'column' => [
            'countries.name' => 10
        ]
    ];

    public function searchText($term)
    {
        return self::search($term);
    }
}
