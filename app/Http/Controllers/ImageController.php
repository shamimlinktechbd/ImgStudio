<?php

namespace App\Http\Controllers;

use App\Models\BackgroundAsset;
use App\Models\ImageActivity;
use App\Models\ImageAsset;
use App\Services\PythonImageProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageController extends Controller
{
    private array $categories = [
        'profile' => 'Profile photo',
        'product' => 'Product',
        'document' => 'Document',
        'social' => 'Social media',
        'portfolio' => 'Portfolio',
    ];

    private array $backgrounds = [
        'original' => 'Original',
        'white' => 'White',
        'transparent' => 'Transparent',
        'studio' => 'Studio grey',
        'sky' => 'Sky blue',
        'forest' => 'Forest green',
    ];

    public function index(Request $request, ?string $category = null)
    {
        $images = $this->ownedImages($request)->latest()->take(12)->get();

        return view('images.index', [
            'images' => $images,
            'categories' => $this->categories,
            'backgrounds' => $this->backgrounds,
            'selectedCategory' => $category,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
            'category' => ['nullable', 'string', 'max:60'],
        ]);

        $file = $data['image'];
        $path = $file->store('images/originals', 'public');
        [$width, $height] = @getimagesize($file->getRealPath()) ?: [null, null];

        $asset = ImageAsset::create([
            'user_id' => optional($request->user())->id,
            'guest_token' => $this->guestToken($request),
            'original_name' => $file->getClientOriginalName(),
            'category' => $data['category'] ?? null,
            'original_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'width' => $width,
            'height' => $height,
            'metadata' => [
                'extension' => $file->getClientOriginalExtension(),
            ],
        ]);

        $this->logActivity($request, $asset, 'uploaded', [
            'category' => $asset->category,
            'filename' => $asset->original_name,
        ]);

        return redirect()->route('images.show', $asset)->with('status', 'Image uploaded. Preview ready.');
    }

    public function show(Request $request, ImageAsset $image)
    {
        $this->authorizeAsset($request, $image);

        return view('images.show', [
            'image' => $image,
            'backgrounds' => $this->backgrounds,
            'backgroundAssets' => BackgroundAsset::where('is_active', true)->latest()->get(),
        ]);
    }

    public function process(Request $request, ImageAsset $image, PythonImageProcessor $processor)
    {
        $this->authorizeAsset($request, $image);

        $data = $request->validate([
            'format' => ['required', 'in:png,jpg,jpeg,webp'],
            'width' => ['nullable', 'integer', 'min:32', 'max:5000'],
            'height' => ['nullable', 'integer', 'min:32', 'max:5000'],
            'background' => ['nullable', 'string', 'max:40'],
            'remove_background' => ['nullable', 'boolean'],
            'background_asset_id' => ['nullable', 'exists:background_assets,id'],
        ]);

        $format = $data['format'] === 'jpeg' ? 'jpg' : $data['format'];
        $destination = 'images/processed/' . $image->id . '-' . now()->format('YmdHis') . '.' . $format;
        Storage::disk('public')->makeDirectory('images/processed');
        $backgroundAsset = ! empty($data['background_asset_id'])
            ? BackgroundAsset::where('is_active', true)->findOrFail($data['background_asset_id'])
            : null;

        try {
            $result = $processor->process(
                storage_path('app/public/' . $image->original_path),
                storage_path('app/public/' . $destination),
                [
                    'format' => $format,
                    'width' => $data['width'] ?? null,
                    'height' => $data['height'] ?? null,
                    'background' => $data['background'] ?? 'original',
                    'remove_background' => $request->boolean('remove_background') || $backgroundAsset !== null,
                    'background_path' => $backgroundAsset ? storage_path('app/public/' . $backgroundAsset->path) : null,
                ]
            );
        } catch (\Throwable $exception) {
            return back()->withErrors([
                'processor' => $exception->getMessage() . ' Install Python and run `pip install -r python-service/requirements.txt`.',
            ]);
        }

        if ($image->processed_path && $image->processed_path !== $image->original_path) {
            Storage::disk('public')->delete($image->processed_path);
        }

        $image->update([
            'processed_path' => $destination,
            'processed_format' => $format,
            'resize_width' => $result['width'] ?? ($data['width'] ?? null),
            'resize_height' => $result['height'] ?? ($data['height'] ?? null),
            'background_category' => $backgroundAsset ? 'admin_asset' : ($data['background'] ?? 'original'),
            'background_asset_id' => optional($backgroundAsset)->id,
            'background_removed' => (bool) ($result['background_removed'] ?? false),
            'last_action' => 'processed',
            'metadata' => array_merge($image->metadata ?? [], ['last_processor' => $result]),
        ]);

        $this->logActivity($request, $image, 'processed', [
            'format' => $format,
            'width' => $data['width'] ?? null,
            'height' => $data['height'] ?? null,
            'background' => $data['background'] ?? 'original',
            'remove_background' => $request->boolean('remove_background') || $backgroundAsset !== null,
            'background_asset_id' => optional($backgroundAsset)->id,
        ]);

        return redirect()->route('images.show', $image)->with('status', 'Image processed successfully.');
    }

    public function history(Request $request)
    {
        $images = $this->ownedImages($request)->with('activities')->latest()->paginate(18);

        return view('images.history', compact('images'));
    }

    public function download(Request $request, ImageAsset $image)
    {
        $this->authorizeAsset($request, $image);

        $this->logActivity($request, $image, 'downloaded', ['path' => $image->displayPath()]);

        return Storage::disk('public')->download($image->displayPath());
    }

    public function destroy(Request $request, ImageAsset $image)
    {
        $this->authorizeAsset($request, $image);

        Storage::disk('public')->delete(array_filter([
            $image->original_path,
            $image->processed_path,
        ]));

        $image->delete();

        return redirect()->route($request->user() && $request->user()->is_admin ? 'admin.images' : 'images.history')
            ->with('status', 'Image deleted.');
    }

    private function ownedImages(Request $request)
    {
        if ($request->user()) {
            return ImageAsset::where('user_id', $request->user()->id);
        }

        return ImageAsset::where('guest_token', $this->guestToken($request));
    }

    private function authorizeAsset(Request $request, ImageAsset $image): void
    {
        if ($request->user() && $request->user()->is_admin) {
            return;
        }

        if ($request->user() && $image->user_id === $request->user()->id) {
            return;
        }

        if (! $request->user() && $image->guest_token === $this->guestToken($request)) {
            return;
        }

        abort(403);
    }

    private function guestToken(Request $request): string
    {
        if (! $request->session()->has('guest_token')) {
            $request->session()->put('guest_token', Str::random(48));
        }

        return $request->session()->get('guest_token');
    }

    private function logActivity(Request $request, ImageAsset $image, string $action, array $parameters = []): void
    {
        ImageActivity::create([
            'image_asset_id' => $image->id,
            'user_id' => optional($request->user())->id,
            'guest_token' => $this->guestToken($request),
            'action' => $action,
            'parameters' => $parameters,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);
    }
}
