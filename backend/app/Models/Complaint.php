<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Complaint extends Model
{
    use SoftDeletes;

    // 🔥 UUID setup
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'title',
        'description',
        'status',
        'location',
    ];

    // 🔥 auto generate UUID + user_id
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->id = Str::uuid();
            $model->user_id = auth()->id();
        });
    }

    // 🔗 relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 🔗 relasi ke gambar
    public function images()
    {
        return $this->hasMany(ComplaintImage::class);
    }
}