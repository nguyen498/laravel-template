<?php


namespace App\Models;


use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

class Website extends Model
{
    use UuidTrait, SearchableTrait;

    protected $table = 'advert_websites';
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $guarded = ['id'];

    protected $fillable = [
        'url',
        'name',
        'notes',
        'status',
        'properties',
//        'created_by',
//        'created_by_name',
//        'updated_by',
//        'updated_by_name',
    ];

    protected $casts = [
        'url'  => 'string',
        'name' => 'string',
        'notes' => 'string',
        'status' => 'int',
        'properties' => 'json',
//        'created_by' => 'string',
//        'created_by_name' => 'string',
//        'updated_by' => 'string',
//        'updated_by_name' => 'string',
    ];

    public function zones()
    {
        return $this->hasMany(Zone::class);
    }
}
