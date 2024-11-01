<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory, UuidTrait;

    protected $table = 'medias';

    protected $primaryKey = 'id';
    public $incrementing = false; // No auto-increment for UUID
    protected $keyType = 'string'; // Set key type to string

    protected $fillable = [
        'id',
        'name',
        'path',
        'width',
        'height',
        'type',
        'sort',
        'mediaable_reference',
        'mediaable_id',
        'mediaable_type',
    ];

    /**
     * Casts for specific attributes.
     */
    protected $casts = [
        'width' => 'float',
        'height' => 'float',
        'type' => 'integer',
        'sort' => 'integer',
    ];

    /**
     * Get the parent mediaable model (if polymorphic).
     */
    public function mediaable()
    {
        return $this->morphTo();
    }
}
