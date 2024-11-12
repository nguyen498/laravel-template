<?php


namespace App\Models;


use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

class Impression extends Model
{
    use UuidTrait, SearchableTrait;
    protected $table = 'advert_impressions';
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'banner_id',
        'zone_id',
        'session_id',
        'clicked',
        'time_viewed',
        'time_clicked',
        'properties',
//        'created_by',
//        'created_by_name',
//        'updated_by',
//        'updated_by_name',
    ];

    protected $casts = [
        'banner_id'  => 'string',
        'zone_id' => 'string',
        'session_id' => 'string',
        'time_viewed' => 'string',
        'time_clicked' => 'string',
        'properties' => 'json',
        'clicked' => 'bool'
//        'created_by' => 'string',
//        'created_by_name' => 'string',
//        'updated_by' => 'string',
//        'updated_by_name' => 'string'
    ];

    /**
     *  Model configuration.
     * @var string
     */
    public $config = 'advert.models.impression';

    protected $guarded = ['id'];

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    public function banner()
    {
        return $this->belongsTo(Banner::class, 'banner_id');
    }

    public function visitorDetail()
    {
        return $this->hasOne(VisitorDetail::class);
    }
}
