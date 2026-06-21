<?php

namespace Tests\Feature;

use App\Models\BackgroundAsset;
use App\Models\ImageAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_loads()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Upload and process image');
    }

    public function test_guest_can_upload_image_and_open_preview()
    {
        Storage::fake('public');

        $response = $this->post('/images', [
            'image' => UploadedFile::fake()->image('avatar.jpg', 600, 400),
            'category' => 'profile',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('image_assets', [
            'original_name' => 'avatar.jpg',
            'category' => 'profile',
            'last_action' => 'uploaded',
        ]);

        $asset = ImageAsset::first();
        Storage::disk('public')->assertExists($asset->original_path);

        $this->get(route('images.show', $asset))
            ->assertStatus(200)
            ->assertSee('avatar.jpg');
    }

    public function test_admin_can_open_dashboard()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertStatus(200)
            ->assertSee('Admin dashboard');
    }

    public function test_admin_can_upload_background_and_user_can_select_it()
    {
        Storage::fake('public');

        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.backgrounds.store'), [
            'name' => 'Studio wall',
            'category' => 'Studio',
            'background' => UploadedFile::fake()->image('wall.jpg', 1200, 800),
        ])->assertRedirect(route('admin.backgrounds'));

        $background = BackgroundAsset::first();
        Storage::disk('public')->assertExists($background->path);

        $this->post('/images', [
            'image' => UploadedFile::fake()->image('portrait.png', 500, 500),
        ]);

        $image = ImageAsset::where('original_name', 'portrait.png')->first();

        $this->get(route('images.show', $image))
            ->assertStatus(200)
            ->assertSee('Studio wall');
    }
}
