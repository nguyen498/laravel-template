<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

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
