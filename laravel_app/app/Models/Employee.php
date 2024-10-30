<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;
use Nicolaslopezj\Searchable\SearchableTrait;

class Employee extends Model implements LaratrustUser
{
    use HasFactory, SoftDeletes, SearchableTrait, HasRolesAndPermissions;

    protected $table = 'employees';

    protected $primaryKey = 'id';
    public $incrementing = false; // No auto-increment for UUID
    protected $keyType = 'string'; // Set key type to string

    protected $fillable = [
        'id',
        'username',
        'password',
        'fullname',
        'email',
        'phone',
        'description',
    ];

    /**
     * Casts for specific attributes.
     */
    protected $casts = [
        'username' => 'string',
        'password' => 'string',
        'fullname' => 'string',
        'email' => 'string',
        'phone' => 'string',
        'description' => 'string',
    ];

    protected $searchable = [
        'column' => [
            'username' => 10,
            'fullname' => 10,
            'email' => 10,
            'phone' => 10,
        ]
    ];
}
