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

    public function url(): string
    {
        return asset('storage/' . $this->displayPath());
    }

    public function originalUrl(): string
    {
        return asset('storage/' . $this->original_path);
    }

    public function formattedSize(): string
    {
        $bytes = $this->size;
        if (! $bytes) {
            return 'N/A';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }
}

