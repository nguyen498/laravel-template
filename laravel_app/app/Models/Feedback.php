<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory, UuidTrait;
    protected $table = 'feedbacks';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'reference',
        'title',
        'content',
        'type',
        'status',
        'medias',
        'confirm_id',
        'confirm_name',
    ];

    protected $casts = [
        'id' => 'string',
        'user_id' => 'string',
        'reference' => 'string',
        'title' => 'string',
        'content' => 'string',
        'type' => 'integer',
        'status' => 'integer',
        'medias' => 'string',
        'confirm_id' => 'string',
        'confirm_name' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
