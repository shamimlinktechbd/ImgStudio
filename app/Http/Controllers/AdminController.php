<?php

namespace App\Http\Controllers;

use App\Models\BackgroundAsset;
use App\Models\ImageActivity;
use App\Models\ImageAsset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard', [
            'imageCount' => ImageAsset::count(),
            'backgroundCount' => BackgroundAsset::count(),
            'userCount' => User::count(),
            'guestImageCount' => ImageAsset::whereNull('user_id')->count(),
            'recentActivities' => ImageActivity::with('image')->latest()->take(10)->get(),
        ]);
    }

    public function images(Request $request)
    {
        $images = ImageAsset::with('user')
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where('original_name', 'like', '%' . $request->q . '%')
                    ->orWhere('category', 'like', '%' . $request->q . '%');
            })
            ->latest()
            ->paginate(24)
            ->withQueryString();
        return view('admin.images', compact('images'));
    }

    public function backgrounds()
    {
        $backgrounds = BackgroundAsset::latest()->paginate(24);
        return view('admin.backgrounds', compact('backgrounds'));
    }

    public function storeBackground(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:80'],
            'background' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $file = $data['background'];
        $path = $file->store('images/backgrounds', 'public');
        [$width, $height] = @getimagesize($file->getRealPath()) ?: [null, null];

        BackgroundAsset::create([
            'user_id' => $request->user()->id,
            'name' => $data['name'],
            'category' => $data['category'] ?? null,
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'width' => $width,
            'height' => $height,
            'is_active' => true,
        ]);

        return redirect()->route('admin.backgrounds')->with('status', 'Background uploaded.');
    }

    public function destroyBackground(BackgroundAsset $background)
    {
        Storage::disk('public')->delete($background->path);
        $background->delete();

        return redirect()->route('admin.backgrounds')->with('status', 'Background deleted.');
    }
}
