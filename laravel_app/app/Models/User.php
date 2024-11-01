<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Nicolaslopezj\Searchable\SearchableTrait;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SearchableTrait, SoftDeletes, UuidTrait, \Laravel\Passport\HasApiTokens;
    public $table = "users";
    public $primaryKey = "id";
    public $incrementing = false;
    protected $keyType = "string";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "email",
        "password",
        "reference",
        "first_name",
        "last_name",
        "phone",
        "description",
        "last_login",
        "type",
        "status",
        'cover',
        'medias'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $searchable = [
        "columns" => [
            "users.name" => 10,
            "users.first_name" => 10,
            "users.last_name" => 10,
            "users.email" => 10,
        ],
    ];

    protected function casts(): array
    {
        return [
            "email_verified_at" => "datetime",
            "password" => "hashed",
            "reference" => "string",
            "first_name" => "string",
            "last_name" => "string",
            "phone" => "string",
            "description" => "string",
            "last_login" => "datetime",
            "type" => "integer",
            "status" => "integer",
            'cover' => 'string',
            'medias' => 'string'
        ];
    }

    public function searchText($term)
    {
        return self::search($term);
    }

    public function userActionPosts()
    {
        return $this->hasMany(UserActionPost::class);
    }

    public function userComments()
    {
        return $this->hasMany(UserComment::class);
    }

    public function userRecentSearchs()
    {
        return $this->hasMany(UserRecentSearch::class);
    }

    public function userSearchs()
    {
        return $this->hasMany(UserSearch::class);
    }

    public function advertisingRequests()
    {
        return $this->hasMany(AdvertisingRequest::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

    public function devices()
    {
        return $this->hasMany(Device::class, 'deviceable_id')
            ->where('deviceable_type', 'users');
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function getNameAttribute()
    {
        return $this->first_name.' '.$this->last_name;
    }

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::needsRehash($password) ? Hash::make($password) : $password;
    }

    public function isDemoUser()
    {
        return $this->email === 'johndoe@example.com';
    }

    public function scopeOrderByName($query)
    {
        $query->orderBy('last_name')->orderBy('first_name');
    }

    public function scopeWhereRole($query, $role)
    {
        switch ($role) {
            case 'user': return $query->where('owner', false);
            case 'owner': return $query->where('owner', true);
        }
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('first_name', 'like', '%'.$search.'%')
                    ->orWhere('last_name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        })->when($filters['role'] ?? null, function ($query, $role) {
            $query->whereRole($role);
        })->when($filters['trashed'] ?? null, function ($query, $trashed) {
            if ($trashed === 'with') {
                $query->withTrashed();
            } elseif ($trashed === 'only') {
                $query->onlyTrashed();
            }
        });
    }
}
