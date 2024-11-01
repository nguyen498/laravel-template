<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostIndustry extends Model
{
    use HasFactory, SoftDeletes, UuidTrait;

    protected $table = 'post_industries';

    protected $primaryKey = 'id';
    public $incrementing = false; // No auto-increment for UUID
    protected $keyType = 'string'; // Set key type to string

    protected $fillable = [
        'id',
        'sub_category_id',
        'reference',
        'name',
        'description',
        'status',
    ];

    /**
     * Casts for specific attributes.
     */
    protected $casts = [
        'status' => 'integer',
    ];

    /**
     * Relationship to SubCategory
     */
    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }
}
