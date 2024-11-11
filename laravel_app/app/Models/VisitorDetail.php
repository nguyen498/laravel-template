<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

class VisitorDetail extends Model
{
    use SearchableTrait;

    protected $table = 'advert_imp_visitor_details';
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'impression_id',
        'user_id',
        'browser',
        'browser_version',
        'is_phone',
        'is_tablet',
        'is_desktop',
        'is_robot',
        'robot',
        'device',
        'platform',
        'platform_version',
        'languages',
        'extras',
        'properties',
//        'created_by',
//        'created_by_name',
//        'updated_by',
//        'updated_by_name',
    ];

    protected $casts = [
        'impression_id' => 'string',
        'browser' => 'string',
        'browser_version' => 'string',
        'robot' => 'string',
        'device' => 'string',
        'platform' => 'string',
        'platform_version' => 'string',
        'properties' => 'string',
        'is_phone' => 'boolean',
        'is_tablet' => 'boolean',
        'is_desktop' => 'boolean',
        'is_robot' => 'boolean',
        'languages' => 'json',
        'extras' => 'json',
//        'created_by' => 'string',
//        'created_by_name' => 'string',
//        'updated_by' => 'string',
//        'updated_by_name' => 'string',
    ];

    protected $guarded = ['id'];

    public function impression()
    {
        return $this->belongsTo(Impression::class, 'impression_id');
    }
}
