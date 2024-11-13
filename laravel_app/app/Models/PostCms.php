<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nicolaslopezj\Searchable\SearchableTrait;

class PostCms extends Model
{
    use HasFactory, SoftDeletes, UuidTrait, SearchableTrait;
    protected $table = 'posts';

    protected $primaryKey = 'id';
    public $incrementing = false; // No auto-increment for UUID
    protected $keyType = 'string'; // Set key type to string

    protected $fillable = [
        'id',
        'reference',
        'category_id',
        'category_name',
        'sub_category_id',
        'sub_category_name',
        'display_type',
        'user_id',
        'type',
        'status',
        'post_industry_id',
        'post_industry_name',
        'title',
        'description',
        'phone_number',
        'email',
        'website',
        'store_name',
        'store_address',
        'store_area',
        'medias',
        'slug',
        'location',
        'start_date',
        'end_date',
        'advert_type'
    ];

    /**
     * Casts for specific attributes.
     */
    protected $casts = [
        'avg_salary' => 'float',
        'min_salary' => 'float',
        'max_salary' => 'float',
        'price' => 'float',
        'num_employees' => 'integer',
        'status' => 'integer',
        'support' => 'integer',
        'display_type' => 'integer',
        'type_salary' => 'integer',
        'job_experience' => 'float',
        'avg_revenue' => 'float',
        'location' => 'json'
    ];

    protected $searchable = [
        'columns' => [
            'posts.reference' => 80,
            'posts.title' => 70,
            'posts.description' => 80
        ]
    ];

    public function searchText($term)
    {
        return self::search($term);
    }

    /**
     * Relationship to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship to Category
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relationship to SubCategory
     */
    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    /**
     * Relationship to PostIndustry
     */
    public function postIndustry()
    {
        return $this->belongsTo(PostIndustry::class);
    }
}
