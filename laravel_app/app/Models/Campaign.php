<?php


namespace App\Models;


use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

class Campaign extends Model
{
    use UuidTrait, SearchableTrait;
    protected $table = 'advert_campaigns';
    protected $primaryKey = 'id';
    public $incrementing = false;

    const STATUS_UNACTIVE       = 0;
    const STATUS_ACTIVE         = 1;
    const STATUS_EXPIRE         = 2;

    /**
     *  Model configuration.
     * @var string
     */
    public $config = 'advert.models.campaign';

    protected $guarded = ['id'];

    protected $fillable = [
        'name',
        'starts_at',
        'ends_at',
        'weight',
        'limit_type',
        'limit_per_day',
        'notes',
        'status',
        'advertiser_id',
        'properties',
//        'created_by',
//        'created_by_name',
//        'updated_by',
//        'updated_by_name',
    ];

    protected $casts = [
        'name'  => 'string',
        'starts_at' => 'string',
        'ends_at' => 'string',
        'weight' => 'string',
        'limit_type' => 'string',
        'limit_per_day' => 'int',
        'status' => 'string',
        'advertiser_id' => 'string',
        'properties' => 'json',
//        'created_by' => 'string',
//        'created_by_name' => 'string',
//        'updated_by' => 'string',
//        'updated_by_name' => 'string'
    ];

    public function advertiser()
    {
        return $this->belongsTo(Advertiser::class, 'advertiser_id');
    }

    public function banners()
    {
        return $this->hasMany(Banner::class);
    }
}
