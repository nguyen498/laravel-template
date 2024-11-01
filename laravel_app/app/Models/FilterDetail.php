<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilterDetail extends Model
{
    use HasFactory, UuidTrait;
    protected $table = 'filter_details';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'filter_id',
        'field_name',
        'label',
        'data',
        'default_value',
        'placeholder',
        'field_type',
    ];

    protected $casts = [
        'id' => 'string',
        'filter_id' => 'string',
        'field_name' => 'string',
        'label' => 'string',
        'data' => 'string', // nếu dữ liệu dài, có thể để lại như vậy
        'default_value' => 'string',
        'placeholder' => 'string',
        'field_type' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function filter (){
        return $this->belongsTo(Filter::class);
    }
}
