<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes, UuidTrait;

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
        'work_position',
        'avg_salary',
        'min_salary',
        'max_salary',
        'type_salary',
        'job_type',
        'job_contract',
        'job_time',
        'job_experience',
        'require_skill',
        'advance_skill',
        'job_environmental',
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
        // Add other casts as needed
    ];

    protected $searchable = [
        'column' => [
            'posts.reference' => 10,
            'posts.title' => 10,
            'posts.description' => 10
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
