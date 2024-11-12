<?php


namespace App\Models;


use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

class Banner extends Model
{
    use UuidTrait, SearchableTrait;
    protected $table = 'advert_banners';
    protected $primaryKey = 'id';
    public $incrementing = false;

    const STATUS_UNACTIVE           = 0;
    const STATUS_ACTIVE             = 1;
    const STATUS_EXPIRE             = 2;

    protected $guarded = ['id'];

    protected $fillable = [
        'name',
        'dimension',
        'type',
        'content',
        'weight',
        'url',
        'notes',
        'status',
        'post_id',
        'campaign_id',
        'properties',
//        'created_by',
//        'created_by_name',
//        'updated_by',
//        'updated_by_name',
    ];

    protected $casts = [
        'name'  => 'string',
        'dimension' => 'string',
        'type' => 'string',
        'content' => 'string',
        'weight' => 'int',
        'url' => 'int',
        'notes' => 'string',
        'status' => 'string',
        'post_id' => 'string',
        'campaign_id' => 'string',
        'properties' => 'json',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    public function zones()
    {
        return $this->belongsToMany(Zone::class, 'advert_banner_zone');
    }
}
