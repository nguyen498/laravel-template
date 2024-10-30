<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Engines\Engine;
use Laravel\Scout\Searchable;
use Nicolaslopezj\Searchable\SearchableTrait;

class User extends Authenticatable
{
    // use HasFactory, Notifiable, SearchableTrait;
    use HasFactory, Notifiable, SearchableTrait, SoftDeletes, UuidTrait;

    public $table = 'users';
    public $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'reference',
        'first_name',
        'last_name',
        'phone',
        'description',
        'last_login',
        'type',
        'status',
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
        'columns' => [
            'users.name' => 10,
            'users.first_name' => 10,
            'users.last_name' => 10,
            'users.email' => 10,
        ],
    ];

//    public function searchableAs(): string
//    {
//        return 'users_index';
//    }
//
//    public function toSearchableArray()
//    {
//        return [
//            'id' => (int) $this->id,
//            'name' => $this->name,
//            'email' => $this->email,
//        ];
//    }


    protected function casts(): array
    {
        return [
            'email_verified_at'     => 'datetime',
            'password'              => 'hashed',
            'reference'             => 'string',
            'first_name'            => 'string',
            'last_name'             => 'string',
            'phone'                 => 'string',
            'description'           => 'string',
            'last_login'            => 'datetime',
            'type'                  => 'integer',
            'status'                => 'integer',
        ];
    }


//    public function searchableUsing(): Engine
//    {
//        return app(EngineManager::class)->engine('meilisearch');
//    }
}
