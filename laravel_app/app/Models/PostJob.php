<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

class PostJob extends Model
{
    use HasFactory, UuidTrait, SearchableTrait;

    protected $table = 'post_jobs';
    protected $keyType = 'string';
    public $incrementing = false;

    const TYPE_RECRUITMENT      = 'recruitment';
    const TYPE_SEARCH_JOB       = 'search_job';

    protected $fillable = [
        'id',
        'post_id',
        'type',
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
    ];

    protected $casts = [
        'id',
        'post_id' => 'string',
        'type' => 'string',
        'work_position' => 'string',
        'avg_salary' => 'float',
        'min_salary' => 'float',
        'max_salary' => 'float',
        'type_salary' => 'int',
        'job_type' => 'string',
        'job_contract' => 'string',
        'job_time' => 'string',
        'job_experience' => 'float',
        'require_skill' => 'json',
        'advance_skill' => 'json',
        'job_environmental' => 'json',
    ];

    protected $searchable = [
        'columns' => [
            'post_jobs.type' => 80,
            'post_jobs.work_position' => 70
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
