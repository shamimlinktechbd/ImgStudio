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

    protected function createValidJpeg(string $name = 'test.jpg'): UploadedFile
    {
        $hex = 'ffd8ffe000104a46494600010101004800480000ffdb004300080606070605080707070909080a0c140d0c0b0b0c1912130f141d1a1f1e1d1a1c1c20242e2720222c231c1c2837292c30313434341f27393d38323c2e333432ffc0000b080010001001011100ffc4001f0000010501010101010100000000000000000102030405060708090a0bffda0008010100003f007f00ffd9';
        $temp = tempnam(sys_get_temp_dir(), 'test_jpeg_');
        file_put_contents($temp, hex2bin($hex));

        return new UploadedFile($temp, $name, 'image/jpeg', null, true);
    }

    public function test_guest_can_upload_image_and_open_preview()
    {
        Storage::fake('public');

        $response = $this->post('/images', [
            'image' => $this->createValidJpeg('avatar.jpg'),
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
            'background' => $this->createValidJpeg('wall.jpg'),
        ])->assertRedirect(route('admin.backgrounds'));

        $background = BackgroundAsset::first();
        Storage::disk('public')->assertExists($background->path);

        $this->post('/images', [
            'image' => $this->createValidJpeg('portrait.png'),
        ]);

        $image = ImageAsset::where('original_name', 'portrait.png')->first();

        $this->get(route('images.show', $image))
            ->assertStatus(200)
            ->assertSee('Studio wall');
    }

    public function test_editor_shows_remove_bg_and_resize_tabs()
    {
        Storage::fake('public');

        $this->post('/images', [
            'image' => $this->createValidJpeg('photo.jpg'),
        ]);

        $image = ImageAsset::where('original_name', 'photo.jpg')->first();

        $response = $this->get(route('images.show', $image));
        $response->assertStatus(200);
        $response->assertSee('Remove BG');
        $response->assertSee('Unblur');
        $response->assertSee('Resize');
        $response->assertSee('Compress');
        $response->assertSee('Choose Backdrop Style');
    }

    public function test_user_can_unblur_image_via_ajax()
    {
        Storage::fake('public');

        $this->post('/images', [
            'image' => $this->createValidJpeg('blurry_photo.jpg'),
        ]);

        $image = ImageAsset::where('original_name', 'blurry_photo.jpg')->first();

        $response = $this->postJson(route('images.unblur', $image), [
            'strength' => 0.75,
            'denoise' => true,
            'contrast_boost' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'last_action' => 'unblurred',
        ]);
        $response->assertJsonStructure([
            'success',
            'imageUrl',
            'originalUrl',
            'last_action',
        ]);

        $image->refresh();
        $this->assertEquals('unblurred', $image->last_action);
        $this->assertNotNull($image->processed_path);
        Storage::disk('public')->assertExists($image->processed_path);
    }

    public function test_ajax_backdrop_processing_returns_json_response()
    {
        Storage::fake('public');

        $this->post('/images', [
            'image' => $this->createValidJpeg('photo.jpg'),
        ]);

        $image = ImageAsset::where('original_name', 'photo.jpg')->first();

        $response = $this->postJson(route('images.process', $image), [
            'format' => 'png',
            'background' => 'studio',
            'remove_background' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'imageUrl',
            'background_removed',
            'background_category',
        ]);
        $response->assertJson([
            'success' => true,
            'background_category' => 'studio',
        ]);
    }

    public function test_login_page_renders_with_modern_design()
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('auth-page-wrapper');
        $response->assertSee('auth-split-card');
        $response->assertSee('Sign In to Studio');
        $response->assertSee('Enterprise Neural AI');
    }

    public function test_register_page_renders_with_modern_design()
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('auth-page-wrapper');
        $response->assertSee('auth-split-card');
        $response->assertSee('Create Free Account');
        $response->assertSee('Zero Limitations');
    }

    public function test_user_can_register_and_redirect_to_studio()
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Demo Designer',
            'email' => 'designer@imagestudio.test',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertRedirect(route('images.index'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'designer@imagestudio.test',
        ]);
    }

    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'user@imagestudio.test',
            'password' => bcrypt('secret1234'),
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'user@imagestudio.test',
            'password' => 'secret1234',
        ]);

        $response->assertRedirect(route('images.index'));
        $this->assertAuthenticatedAs($user);
    }
}
