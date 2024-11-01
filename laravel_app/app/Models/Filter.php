<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Filter extends Model
{
    use HasFactory, UuidTrait;
    protected $table = 'filters';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'reference',
        'category_id',
        'created_id',
        'created_name',
        'status',
    ];

    protected $casts = [
        'id' => 'string',
        'category_id' => 'string',
        'created_id' => 'string',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function filterDetails(){
        return $this->hasMany(FilterDetail::class);
    }
}
