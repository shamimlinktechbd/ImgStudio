<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImageActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_asset_id',
        'user_id',
        'guest_token',
        'action',
        'parameters',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'parameters' => 'array',
    ];

    public function image()
    {
        return $this->belongsTo(ImageAsset::class, 'image_asset_id');
    }
}
