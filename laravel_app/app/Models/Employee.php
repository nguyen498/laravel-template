<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;
use Nicolaslopezj\Searchable\SearchableTrait;

class Employee extends Model implements LaratrustUser
{
    use HasFactory, SoftDeletes, SearchableTrait, HasRolesAndPermissions, UuidTrait;

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
            'employees.username' => 10,
            'employees.fullname' => 10,
            'employees.email' => 10,
            'employees.phone' => 10,
        ]
    ];

    public function searchText($term)
    {
        return self::search($term);
    }
}
