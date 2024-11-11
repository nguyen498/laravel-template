<?php


namespace App\Models;


use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

class Zone extends Model
{
    use UuidTrait, SearchableTrait;
    protected $table = 'advert_zones';
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $guarded = ['id'];

    const TYPE_BOOST            = 1;
    const TYPE_HORIZONTAL       = 2;
    const TYPE_POPUP            = 3;

    const STATUS_UNACTIVE   = 0;
    const STATUS_ACTIVE     = 1;

    protected $fillable = [
        'name',
        'key',
        'dimension',
        'notes',
        'type',
        'status',
        'website_id',
        'properties',
        'status',
        'website_id',
        'properties',
//        'created_by',
//        'created_by_name',
//        'updated_by',
//        'updated_by_name',
    ];

    protected $casts = [
        'name'  => 'string',
        'key' => 'string',
        'dimension' => 'string',
        'notes' => 'string',
        'type'  => 'int',
        'status' => 'int',
        'website_id' => 'int',
        'properties' => 'string',
//        'created_by' => 'string',
//        'created_by_name' => 'string',
//        'updated_by' => 'string',
//        'updated_by_name' => 'string'
    ];

    public function banners()
    {
        return $this->belongsToMany(Banner::class, 'advert_banner_zone');
    }

    public function website()
    {
        return $this->belongsTo(Website::class, 'website_id');
    }
}
