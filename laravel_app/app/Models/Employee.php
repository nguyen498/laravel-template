<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;
use Nicolaslopezj\Searchable\SearchableTrait;
use Laravel\Passport\HasApiTokens;

class Employee extends Authenticatable implements LaratrustUser
{
    use HasFactory, SoftDeletes, SearchableTrait, HasRolesAndPermissions, UuidTrait, HasApiTokens;

    protected $table = 'employees';

    const STATUS_UNACTIVE   = 0;
    const STATUS_ACTIVE     = 1;

    const TYPE_NORMAL       = 1;
    const TYPE_SUPER_ADMIN  = 2;

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
        'type',
        'status',
        'last_login'
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
        'last_login' => 'datetime',
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
