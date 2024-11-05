<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory, UuidTrait;
    const pre_fix = 'CAT';

    //type
    const HOME_OWNER = 1;
    const COMMUNITY = 2;

    // status
    const STATUS_UNACTIVE   = 0;
    const STATUS_ACTIVE     = 1;

    protected $table = 'categories';

    protected $primaryKey = 'id';
    public $incrementing = false; // No auto-increment for UUID
    protected $keyType = 'string'; // Set key type to string



    protected $fillable = [
        'id',
        'reference',
        'name',
        'description',
        'type',
        'status',
    ];

    /**
     * Casts for specific attributes.
     */
    protected $casts = [
        'type' => 'integer',
        'status' => 'integer',
    ];

    /**
     * Relationship to Sub Categories
     */
    public function subCategories()
    {
        return $this->hasMany(SubCategory::class);
    }
}
