<?php

namespace Tests\Feature;

use App\Models\ImageAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageCompressionTest extends TestCase
{
    use RefreshDatabase;

    protected function createValidJpeg(string $name = 'test.jpg', int $targetBytes = 50000): UploadedFile
    {
        // 16x16 standard minimal valid JPEG with SOF0 header
        $hex = 'ffd8ffe000104a46494600010101004800480000ffdb004300080606070605080707070909080a0c140d0c0b0b0c1912130f141d1a1f1e1d1a1c1c20242e2720222c231c1c2837292c30313434341f27393d38323c2e333432ffc0000b080010001001011100ffc4001f0000010501010101010100000000000000000102030405060708090a0bffda0008010100003f007f00ffd9';
        $binary = hex2bin($hex);

        if ($targetBytes > strlen($binary)) {
            $commentLen = $targetBytes - strlen($binary);
            if ($commentLen > 4) {
                // Insert COM (Comment) marker: 0xFFFE + 2 bytes length + data
                $comment = "\xFF\xFE" . pack('n', $commentLen - 2) . str_repeat('X', $commentLen - 4);
                $binary = substr($binary, 0, 2) . $comment . substr($binary, 2);
            }
        }

        $temp = tempnam(sys_get_temp_dir(), 'test_jpeg_');
        file_put_contents($temp, $binary);

        return new UploadedFile($temp, $name, 'image/jpeg', null, true);
    }

    public function test_compress_page_loads_successfully()
    {
        $response = $this->get('/compress');

        $response->assertStatus(200);
        $response->assertSee('Smart Image Compressor');
        $response->assertSee('Reduce Image Size');
    }

    public function test_can_compress_image_directly_to_target_kb()
    {
        Storage::fake('public');

        $file = $this->createValidJpeg('photo.jpg', 65000);

        $response = $this->post('/compress', [
            'image' => $file,
            'target_kb' => 20,
            'format' => 'jpg',
            'compression_type' => 'target_kb',
        ]);

        $response->assertRedirect();

        $asset = ImageAsset::where('original_name', 'photo.jpg')->first();
        $this->assertNotNull($asset);
        $this->assertEquals('compressed', $asset->last_action);
        $this->assertNotNull($asset->processed_path);

        Storage::disk('public')->assertExists($asset->processed_path);

        // Verify that file size on disk is within target size ceiling
        $processedSize = Storage::disk('public')->size($asset->processed_path);
        $this->assertLessThanOrEqual(25 * 1024, $processedSize);
    }

    public function test_can_compress_existing_asset_from_studio_editor()
    {
        Storage::fake('public');

        $file = $this->createValidJpeg('profile.jpg', 80000);

        $uploadResponse = $this->post('/images', [
            'image' => $file,
            'category' => 'profile',
        ]);

        $asset = ImageAsset::where('original_name', 'profile.jpg')->first();
        $this->assertNotNull($asset);

        // Compress to 30 KB target
        $compressResponse = $this->post(route('images.compress', $asset), [
            'compression_type' => 'target_kb',
            'target_kb' => 30,
            'format' => 'jpg',
        ]);

        $compressResponse->assertRedirect(route('images.show', $asset));

        $asset->refresh();
        $this->assertEquals('compressed', $asset->last_action);
        $this->assertNotNull($asset->processed_path);

        Storage::disk('public')->assertExists($asset->processed_path);
        $processedSize = Storage::disk('public')->size($asset->processed_path);
        $this->assertLessThanOrEqual(32 * 1024, $processedSize);
    }

    public function test_can_compress_with_quality_slider()
    {
        Storage::fake('public');

        $file = $this->createValidJpeg('landscape.jpg', 50000);

        $uploadResponse = $this->post('/images', [
            'image' => $file,
        ]);

        $asset = ImageAsset::where('original_name', 'landscape.jpg')->first();
        $this->assertNotNull($asset);

        $compressResponse = $this->post(route('images.compress', $asset), [
            'compression_type' => 'quality',
            'quality' => 50,
            'format' => 'webp',
        ]);

        $compressResponse->assertRedirect(route('images.show', $asset));

        $asset->refresh();
        $this->assertEquals('compressed', $asset->last_action);
        $this->assertEquals('webp', $asset->processed_format);
        Storage::disk('public')->assertExists($asset->processed_path);
    }

    public function test_home_page_shows_compressor_link_and_promo()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Compress Image');
        $response->assertSee('Need to reduce image size (KB)?');
    }
}
