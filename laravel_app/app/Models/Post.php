<?php

namespace App\Models;

use App\Traits\UuidTrait;
use App\Utils\LogHelper;
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
        'location',
        'start_date',
        'end_date',
        'advert_type'
    ];

    /**
     * Casts for specific attributes.
     */
    protected $casts = [
        'id' => 'string',
        'reference' => 'string',
        'category_id' => 'string',
        'category_name' => 'string',
        'sub_category_id' => 'string',
        'sub_category_name' => 'string',
        'display_type' => 'int',
        'user_id' => 'string',
        'type' => 'int',
        'status' => 'int',
        'post_industry_id' => 'string',
        'post_industry_name' => 'string',
        'title' => 'string',
        'description' => 'string',
        'phone_number' => 'string',
        'email' => 'string',
        'website' => 'string',
        'store_name' => 'string',
        'store_address' => 'string',
        'store_area' => 'string',
        'medias' => 'json',
        'slug' => 'string',
        'location' => 'json',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'advert_type' => 'string'
    ];

    protected $searchable = [
        'column' => [
            'posts.reference' => 80,
            'posts.title' => 80,
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

    public function userComments()
    {
        return $this->hasMany(UserComment::class, 'object_id')->where('object_type', 'posts');
    }

    /**
     * Relationship to PostSale
     */
    public function postSale()
    {
        return $this->hasOne(PostSale::class);
    }

    /**
     * Relationship to PostJob
     */
    public function postJob()
    {
        return $this->hasOne(PostJob::class);
    }

    public function searchableAs()
    {
        return 'posts_index';
    }

    public function toSearchableArray()
    {
        // TODO: Implement toSearchableArray() method.
        $post_job = [];
        $post_sale = [];
        if(in_array($this->type, [Post::TYPE_TIM_VIEC, Post::TYPE_TUYEN_DUNG])){
            if(isset($this->postJob)){
                $post_job = json_decode(json_encode($this->postJob), true);
                if(isset($post_job['job_contract'])){
                    $post_job['job_contract'] = json_decode($post_job['job_contract'], true);
                }
                if(isset($post_job['job_time'])){
                    $post_job['job_time'] = json_decode($post_job['job_time'], true);
                }
                if(isset($post_job['require_skill'])){
                    $post_job['require_skill'] = json_decode($post_job['require_skill'], true);
                }
                if(isset($post_job['advance_skill'])){
                    $post_job['advance_skill'] = json_decode($post_job['advance_skill'], true);
                }
                if(isset($post_job['job_environmental'])){
                    $post_job['job_environmental'] = json_decode($post_job['job_environmental'], true);
                }
            }
        }
        else if(in_array($this->type, [Post::TYPE_SELL, Post::TYPE_BUY])){
            if(isset($this->postSale)){
                $post_sale = json_decode(json_encode($this->postSale), true);

                if(isset($post_sale['nearby_areas'])){
                    $post_sale['nearby_areas'] = json_decode($post_sale['nearby_areas'], true);
                }
                if(isset($post_sale['lease_agreement'])){
                    $post_sale['lease_agreement'] = json_decode($post_sale['lease_agreement'], true);
                }
                if(isset($post_sale['facilities'])){
                    $post_sale['facilities'] = json_decode($post_sale['facilities'], true);
                }
            }
        }

        LogHelper::writeLog('$post_sale =========>  ' . json_encode($post_sale), 1);
        LogHelper::writeLog('$post_job =========>  ' . json_encode($post_job), 1);
        LogHelper::writeLog('$post_job =========>  ' . gettype($this->advert_type), 1);
        return [
            'id' => $this->id ?? null,
            'reference' => $this->reference ?? null,
            'category_id' => $this->category_id ?? null,
            'category_name' => $this->category_name ?? null,
            'sub_category_id' => $this->sub_category_id ?? null,
            'sub_category_name' => $this->sub_category_name ?? null,
            'display_type' => $this->display_type ?? null,
            'user_id' => $this->user_id ?? null,
            'type' => $this->type ?? null,
            'status' => $this->status ?? null,
            'post_industry_id' => $this->post_industry_id ?? null,
            'post_industry_name' => $this->post_industry_name ?? null,
            'title' => $this->title ?? null,
            'description' => $this->description ?? null,
            'phone_number' => $this->phone_number ?? null,
            'email' => $this->email ?? null,
            'website' => $this->website ?? null,
            'store_name' => $this->store_name ?? null,
            'store_address' => $this->store_address ?? null,
            'store_area' => $this->store_area ?? null,
            'medias' => isset($this->medias) ? json_decode($this->medias, true) : null, // Chuyển từ JSON string thành mản ?? 'g
            'slug' => $this->slug ?? null,
//            'location' =>  isset($this->location) ? json_decode($this->location, true) : '', // Dữ liệu dạng JSON sẽ được lưu thành array
            'location' =>  $this->location ?? null, // Dữ liệu dạng JSON sẽ được lưu thành array
            'advert_type' => isset($this->advert_type) ? json_decode($this->advert_type, true) : null,
            'post_sale' => $post_sale,
            'post_job' => $post_job,


//            'work_position' => $this->work_position ?? ',
//            'avg_salary' => $this->avg_salary ?? ',
//            'min_salary' => $this->min_salary ?? null,
//            'max_salary' => $this->max_salary ?? null,
//            'type_salary' => $this->type_salary ?? null,
//            'job_type' => $this->job_type ?? null,
//            'job_contract' => $this->job_contract ?? null,
//            'job_time' =>  isset($this->job_time) ? json_decode($this->job_time, true) : null, // Dữ liệu dạng JSON sẽ được lưu thành array
//            'job_experience' => $this->job_experience ?? null,
//            'require_skill' =>  isset($this->require_skill) ? json_decode($this->require_skill, true) : null,
//            'advance_skill' =>  isset($this->advance_skill) ? json_decode($this->advance_skill, true) : null,
//            'job_environmental' => $this->job_environmental ?? null,
//            'business_type' => $this->business_type ?? null,
//            'facebook_name' => $this->facebook_name ?? null,
//            'facebook_url' => $this->facebook_url ?? null,
//            'instagram_name' => $this->instagram_name ?? null,
//            'instagram_url' => $this->instagram_url ?? null,
//            'facilities' => isset($this->facilities) ? json_decode($this->facilities) : null,
//            'num_employees' => $this->num_employees ?? null,
//            'price' => $this->price ?? null,
//            'lease_agreement' => isset($this->lease_agreement) ? json_decode($this->lease_agreement, true) : null, // Đảm bảo lưu dưới dạng array
//            'avg_revenue' => $this->avg_revenue ?? null,
//            'support' => $this->support ?? null,
//            'additional_infor' => isset($this->additional_infor) ? json_decode($this->additional_infor) : null,
//            'deleted_at' => $this->deleted_at ?? null,
//            'created_at' => $this->created_at ?? null,
//            'updated_at' => $this->updated_at ?? null,
//            'nearby_areas' => isset($this->nearby_areas) ? json_decode($this->nearby_areas) : null,
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
            'post_sale' => [
                "type" => 'object',
                "properties"=> [
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
                ]
            ],
            'post_job' => [
                "type" => 'object',
                "properties"=> [
                    'work_position' => 'keyword',
                    'job_type' => 'keyword',
                    'job_contract' => 'keyword',
                    'job_time' => 'keyword',
                    'require_skill' => 'keyword',
                    'advance_skill' => 'keyword',
                    'job_environmental' => 'keyword',
                    'avg_salary' => 'integer',
                    'min_salary' => 'integer',
                    'max_salary' => 'integer',
                ]
            ],
            'created_at' => 'date',
            'advert_type' => 'keyword',
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
