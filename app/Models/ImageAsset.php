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
        return self::formatBytes((int) $this->size);
    }

    public function currentSizeBytes(): int
    {
        if ($this->processed_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->processed_path)) {
            return (int) \Illuminate\Support\Facades\Storage::disk('public')->size($this->processed_path);
        }

        return (int) $this->size;
    }

    public function formattedCurrentSize(): string
    {
        return self::formatBytes($this->currentSizeBytes());
    }

    public function savingsPercent(): ?float
    {
        if (! $this->processed_path || ! $this->size) {
            return null;
        }

        $current = $this->currentSizeBytes();
        if ($current >= $this->size) {
            return 0.0;
        }

        return round((($this->size - $current) / $this->size) * 100, 1);
    }

    public static function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
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

