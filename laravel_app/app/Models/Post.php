<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use JeroenG\Explorer\Application\Aliased;
use JeroenG\Explorer\Application\Explored;
use JeroenG\Explorer\Application\IndexSettings;
use Laravel\Scout\Searchable;

class Post extends Model implements Explored, IndexSettings, Aliased
{
    use HasFactory, SoftDeletes, UuidTrait, Searchable;
    const pre_fix = 'POST';

    //type
    const TYPE_TUYEN_DUNG   = 1;
    const TYPE_TIM_VIEC     = 2;
    const TYPE_SELL         = 3;
    const TYPE_BUY          = 4;

    //type_salary
    const TYPE_NGAY          = 1;
    const TYPE_TUAN          = 2;
    const TYPE_THANG         = 3;

    //job_type
    const JOB_TYPE_FULL_TIME      = 1;
    const JOB_TYPE_PART_TIME      = 2;
    const JOB_TYPE_ONLINE         = 3;

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
        'location',
        'num_tables',
        'num_chairs',
        'num_rooms'
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
        'location' => 'json',
        'lease_agreement' => 'array',
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

    public function userComments()
    {
        return $this->hasMany(UserComment::class, 'object_id')->where('object_type', 'posts');
    }

    public function searchableAs()
    {
        return 'posts_index';
    }

    public function toSearchableArray()
    {
        // TODO: Implement toSearchableArray() method.
        return [
            'id' => $this->id ?? '',
            'reference' => $this->reference ?? '',
            'category_id' => $this->category_id ?? '',
            'category_name' => $this->category_name ?? '',
            'sub_category_id' => $this->sub_category_id ?? '',
            'sub_category_name' => $this->sub_category_name ?? '',
            'display_type' => $this->display_type ?? '',
            'user_id' => $this->user_id ?? '',
            'type' => $this->type ?? '',
            'status' => $this->status ?? '',
            'post_industry_id' => $this->post_industry_id ?? '',
            'post_industry_name' => $this->post_industry_name ?? '',
            'title' => $this->title ?? '',
            'description' => $this->description ?? '',
            'phone_number' => $this->phone_number ?? '',
            'email' => $this->email ?? '',
            'website' => $this->website ?? '',
            'store_name' => $this->store_name ?? '',
            'store_address' => $this->store_address ?? '',
            'store_area' => $this->store_area ?? '',
            'medias' => isset($this->medias) ? json_decode($this->medias, true) : '', // Chuyển từ JSON string thành mản ?? 'g
            'slug' => $this->slug ?? '',
            'work_position' => $this->work_position ?? '',
            'avg_salary' => $this->avg_salary ?? '',
            'min_salary' => $this->min_salary ?? '',
            'max_salary' => $this->max_salary ?? '',
            'type_salary' => $this->type_salary ?? '',
            'job_type' => $this->job_type ?? '',
            'job_contract' => $this->job_contract ?? '',
            'job_time' =>  isset($this->job_time) ? json_decode($this->job_time, true) : '', // Dữ liệu dạng JSON sẽ được lưu thành array
            'job_experience' => $this->job_experience ?? '',
            'require_skill' =>  isset($this->require_skill) ? json_decode($this->require_skill, true) : '',
            'advance_skill' =>  isset($this->advance_skill) ? json_decode($this->advance_skill, true) : '',
            'job_environmental' => $this->job_environmental ?? '',
            'business_type' => $this->business_type ?? '',
            'facebook_name' => $this->facebook_name ?? '',
            'facebook_url' => $this->facebook_url ?? '',
            'instagram_name' => $this->instagram_name ?? '',
            'instagram_url' => $this->instagram_url ?? '',
            'facilities' => isset($this->facilities) ? json_decode($this->facilities) : '',
            'num_employees' => $this->num_employees ?? '',
            'price' => $this->price ?? '',
            'lease_agreement' => isset($this->lease_agreement) ? json_decode($this->lease_agreement, true) : '', // Đảm bảo lưu dưới dạng array
            'avg_revenue' => $this->avg_revenue ?? '',
            'support' => $this->support ?? '',
            'additional_infor' => isset($this->additional_infor) ? json_decode($this->additional_infor) : '',
            'deleted_at' => $this->deleted_at ?? '',
            'created_at' => $this->created_at ?? '',
            'updated_at' => $this->updated_at ?? '',
            'nearby_areas' => isset($this->nearby_areas) ? json_decode($this->nearby_areas) : '',
        ];
    }

    public function mappableAs(): array
    {
        return [
            'id' => 'keyword',
            'sub_category_id' => 'keyword',
            'category_id' => 'keyword',
            'user_id' => 'keyword',
            'title' => [
                'type' => 'text',
                'analyzer' => 'post_analyzer',
            ],
            'description' => [
                'type' => 'text',
                'analyzer' => 'post_analyzer',
            ],
            "location" => [
                'type' => 'geo_point',
            ],
            "nearby_areas" => [
                'type' => 'keyword',
            ],
            'lease_agreement' => [
                'type' => 'object',
                'properties' => [
                    'money_rent' => ['type' => 'float'],
                    'lease_remaining' => ['type' => 'integer'],
                    'more_info' => ['type' => 'text'],
                ]
            ],
            'facilities' => [
                'type' => 'object',
                'properties' => [
                    'num_tables' => ['type' => 'integer'],
                    'num_chairs' => ['type' => 'integer'],
                    'num_rooms' => ['type' => 'integer'],
                    'utilities' => [
                        'type' => 'keyword'
                    ],
                ]
            ],
            'created_at' => 'date',
        ];
    }

    public function indexSettings(): array
    {
        return [
            'index' => [
                'number_of_shards' => 2,
            ],
            'analysis' => [
                "char_filter" => [
                    "vi_char_filter" => [
                        "type" => "mapping",
                        "mappings" => [
                            "á => a",
                            "à => a",
                            "ả => a",
                            "ã => a",
                            "ạ => a",
                            "ă => a",
                            "ắ => a",
                            "ằ => a",
                            "ẳ => a",
                            "ẵ => a",
                            "ặ => a",
                            "â => a",
                            "ấ => a",
                            "ầ => a",
                            "ẩ => a",
                            "ẫ => a",
                            "ậ => a",
                            "é => e",
                            "è => e",
                            "ẻ => e",
                            "ẽ => e",
                            "ẹ => e",
                            "ê => e",
                            "ế => e",
                            "ề => e",
                            "ể => e",
                            "ễ => e",
                            "ệ => e",
                            "í => i",
                            "ì => i",
                            "ỉ => i",
                            "ĩ => i",
                            "ị => i",
                            "ó => o",
                            "ò => o",
                            "ỏ => o",
                            "õ => o",
                            "ọ => o",
                            "ô => o",
                            "ố => o",
                            "ồ => o",
                            "ổ => o",
                            "ỗ => o",
                            "ộ => o",
                            "ơ => o",
                            "ớ => o",
                            "ờ => o",
                            "ở => o",
                            "ỡ => o",
                            "ợ => o",
                            "ú => u",
                            "ù => u",
                            "ủ => u",
                            "ũ => u",
                            "ụ => u",
                            "ư => u",
                            "ứ => u",
                            "ừ => u",
                            "ử => u",
                            "ữ => u",
                            "ự => u",
                            "ý => y",
                            "ỳ => y",
                            "ỷ => y",
                            "ỹ => y",
                            "ỵ => y",
                            "đ => d",
                        ],
                    ]
                ],
                "analyzer" => [
                    "post_analyzer" => [
                        "type" => "custom",
                        "tokenizer" => "vi_tokenizer",
                        "char_filter" => ["vi_char_filter"],
                        "filter" => ["lowercase"],
                    ]
                ],
            ],
        ];
    }
}
