<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

class PostSale extends Model
{
    use HasFactory, UuidTrait, SearchableTrait;

    protected $table = 'post_sales';
    protected $keyType = 'string';
    public $incrementing = false;

    const TYPE_SELL      = 'sell';
    const TYPE_BUY       = 'buy';

    protected $fillable = [
        'id',
        'post_id',
        'type',
        'business_type',
        'facebook_name',
        'facebook_url',
        'instagram_name',
        'instagram_url',
        'facilities',
        'num_employees',
        'price',
        'lease_agreement',
        'avg_revenue',
        'support',
        'additional_infor',
        'nearby_areas'
    ];

    protected $casts = [
        'post_id' => 'string',
        'type' => 'string',
        'business_type' => 'string',
        'facebook_name' => 'string',
        'facebook_url' => 'string',
        'instagram_name' => 'string',
        'instagram_url' => 'string',
        'facilities' => 'json',
        'num_employees' => 'int',
        'price' => 'float',
        'lease_agreement' => 'json',
        'avg_revenue' => 'float',
        'support' => 'int',
        'additional_infor' => 'string',
        'nearby_areas' => 'string'
    ];

    protected $searchable = [
        'columns' => [
            'post_sales.type' => 80,
            'post_sales.business_type' => 70
        ]
    ];

    /**
     * Relationship to User
     */
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function searchText($term)
    {
        return self::search($term);
    }
}
