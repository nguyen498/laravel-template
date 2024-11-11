<?php


namespace App\Models;


use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

class Advertiser extends Model
{
    use UuidTrait, SearchableTrait;
    protected $table = 'advert_advertisers';
    protected $primaryKey = 'id';
    public $incrementing = false;

    /**
     *  Model configuration.
     * @var string
     */
    public $config = 'advert.models.advertiser';

    protected $fillable = [
        'name',
//        'contact',
        'email',
        'phone',
        'notes',
        'status',
        'properties',
        'user_id',
//        'created_by',
//        'created_by_name',
//        'updated_by',
//        'updated_by_name',
    ];

    protected $casts = [
        'name'  => 'string',
//        'contact' => 'string',
        'email' => 'string',
        'phone' => 'string',
        'notes' => 'string',
        'status' => 'int',
        'properties' => 'json',
        'user_id' => 'string',
    ];

    protected $guarded = ['id'];

    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    public function post()
    {
        return $this->hasOne(Post::class, 'post_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'user_id');
    }
}
