<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImageAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guest_token',
        'original_name',
        'category',
        'background_category',
        'background_asset_id',
        'background_removed',
        'original_path',
        'processed_path',
        'mime_type',
        'size',
        'width',
        'height',
        'processed_format',
        'resize_width',
        'resize_height',
        'last_action',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'background_removed' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function activities()
    {
        return $this->hasMany(ImageActivity::class);
    }

    public function backgroundAsset()
    {
        return $this->belongsTo(BackgroundAsset::class);
    }

    public function displayPath()
    {
        return $this->processed_path ?: $this->original_path;
    }
}
