<?php

namespace App\Services;

use RuntimeException;
use Throwable;

class PythonImageProcessor
{
    public function process(string $source, string $destination, array $options): array
    {
        $python = $this->resolvePythonBinary();
        $script = base_path('python-service/processor.py');

        $payload = json_encode([
            'source' => $source,
            'destination' => $destination,
            'format' => $options['format'] ?? 'png',
            'width' => $options['width'] ?? null,
            'height' => $options['height'] ?? null,
            'mode' => $options['mode'] ?? 'stretch',
            'background' => $options['background'] ?? 'original',
            'remove_background' => (bool) ($options['remove_background'] ?? false),
            'background_path' => $options['background_path'] ?? null,
        ]);

        $encodedPayload = base64_encode($payload);

        $command = implode(' ', [
            escapeshellarg($python),
            escapeshellarg($script),
            escapeshellarg($encodedPayload),
        ]);

        $output = [];
        $exitCode = 0;
        exec($command . ' 2>&1', $output, $exitCode);

        if ($exitCode === 0) {
            $jsonStr = implode("\n", $output);
            $result = json_decode($jsonStr, true);
            if (is_array($result) && isset($result['path'])) {
                return $result;
            }
        }

        $errorMsg = implode("\n", $output);

        // If AI background removal or custom background path was requested, we need Python
        if (! empty($options['remove_background']) || ! empty($options['background_path'])) {
            throw new RuntimeException('Python image processor failed: ' . $errorMsg);
        }

        // Fallback to PHP GD if available
        if (extension_loaded('gd')) {
            try {
                return $this->processWithGd($source, $destination, $options);
            } catch (Throwable $e) {
                throw new RuntimeException('Image processing failed (Python: ' . $errorMsg . ' | GD: ' . $e->getMessage() . ')');
            }
        }

        throw new RuntimeException('Python image processor failed: ' . $errorMsg);
    }

    protected function resolvePythonBinary(): string
    {
        $configured = config('services.python.binary', 'python');
        if ($configured && file_exists($configured)) {
            return $configured;
        }

        $userProfile = getenv('USERPROFILE') ?: 'C:\\Users\\' . get_current_user();
        $candidates = array_filter([
            $configured,
            $userProfile . '\\AppData\\Local\\Python\\pythoncore-3.14-64\\python.exe',
            $userProfile . '\\AppData\\Local\\Programs\\Python\\Python314\\python.exe',
            $userProfile . '\\AppData\\Local\\Programs\\Python\\Python313\\python.exe',
            $userProfile . '\\AppData\\Local\\Programs\\Python\\Python312\\python.exe',
            $userProfile . '\\AppData\\Local\\Programs\\Python\\Python311\\python.exe',
            'python',
            'py',
        ]);

        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        return 'python';
    }

    protected function processWithGd(string $source, string $destination, array $options): array
    {
        $contents = file_get_contents($source);
        if ($contents === false) {
            throw new RuntimeException("Could not read image from {$source}");
        }

        $srcImg = @imagecreatefromstring($contents);
        if (! $srcImg) {
            throw new RuntimeException("Unsupported image format for GD processing");
        }

        $origW = imagesx($srcImg);
        $origH = imagesy($srcImg);

        $targetW = ! empty($options['width']) ? (int) $options['width'] : null;
        $targetH = ! empty($options['height']) ? (int) $options['height'] : null;
        $mode = $options['mode'] ?? 'stretch';

        if (! $targetW && ! $targetH) {
            $newW = $origW;
            $newH = $origH;
        } elseif ($targetW && ! $targetH) {
            $newW = $targetW;
            $newH = max(1, (int) round($origH * ($targetW / $origW)));
        } elseif ($targetH && ! $targetW) {
            $newH = $targetH;
            $newW = max(1, (int) round($origW * ($targetH / $origH)));
        } else {
            if ($mode === 'contain') {
                $ratio = min($targetW / $origW, $targetH / $origH);
                $newW = max(1, (int) round($origW * $ratio));
                $newH = max(1, (int) round($origH * $ratio));
            } else {
                $newW = $targetW;
                $newH = $targetH;
            }
        }

        $resized = imagescale($srcImg, $newW, $newH, IMG_LANCZOS);
        imagedestroy($srcImg);

        if (! $resized) {
            throw new RuntimeException("Failed to resize image using GD");
        }

        $format = strtolower($options['format'] ?? 'png');
        if ($format === 'jpeg') {
            $format = 'jpg';
        }

        $bgOption = $options['background'] ?? 'original';
        $bgColors = [
            'white' => [255, 255, 255],
            'studio' => [232, 236, 238],
            'sky' => [210, 232, 250],
            'forest' => [214, 232, 216],
        ];

        // Create canvas
        $canvas = imagecreatetruecolor($newW, $newH);

        if ($format === 'png' || $format === 'webp') {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            if ($bgOption !== 'original' && $bgOption !== 'transparent' && isset($bgColors[$bgOption])) {
                [$r, $g, $b] = $bgColors[$bgOption];
                $color = imagecolorallocate($canvas, $r, $g, $b);
                imagefilledrectangle($canvas, 0, 0, $newW, $newH, $color);
                imagealphablending($canvas, true);
                imagecopy($canvas, $resized, 0, 0, 0, 0, $newW, $newH);
            } else {
                $trans = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
                imagefill($canvas, 0, 0, $trans);
                imagealphablending($canvas, true);
                imagecopy($canvas, $resized, 0, 0, 0, 0, $newW, $newH);
            }
        } else {
            // JPG
            [$r, $g, $b] = $bgColors[$bgOption] ?? [255, 255, 255];
            $color = imagecolorallocate($canvas, $r, $g, $b);
            imagefilledrectangle($canvas, 0, 0, $newW, $newH, $color);
            imagecopy($canvas, $resized, 0, 0, 0, 0, $newW, $newH);
        }
        imagedestroy($resized);

        @mkdir(dirname($destination), 0777, true);

        if ($format === 'jpg') {
            imagejpeg($canvas, $destination, 92);
        } elseif ($format === 'webp') {
            imagewebp($canvas, $destination, 88);
        } else {
            imagepng($canvas, $destination, 6);
        }
        imagedestroy($canvas);

        return [
            'path' => $destination,
            'width' => $newW,
            'height' => $newH,
            'format' => $format,
            'background' => $bgOption,
            'background_asset' => null,
            'background_removed' => false,
        ];
    }
}

