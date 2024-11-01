<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    use HasFactory, UuidTrait;

    protected $table = 'sub_categories';

    protected $primaryKey = 'id';
    public $incrementing = false; // No auto-increment for UUID
    protected $keyType = 'string'; // Set key type to string

    protected $fillable = [
        'id',
        'category_id',
        'name',
        'description',
        'logo',
        'status',
    ];

    /**
     * Casts for specific attributes.
     */
    protected $casts = [
        'status' => 'integer',
    ];

    /**
     * Relationship to Category
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function postIndustry()
    {
        return $this->hasMany(PostIndustry::class);
    }
}
