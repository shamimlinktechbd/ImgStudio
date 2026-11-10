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
            'cutout_source' => $options['cutout_source'] ?? null,
            'cutout_destination' => $options['cutout_destination'] ?? null,
            'background_path' => $options['background_path'] ?? null,
            'target_kb' => $options['target_kb'] ?? null,
            'quality' => $options['quality'] ?? null,
            'unblur' => (bool) ($options['unblur'] ?? false),
            'unblur_strength' => $options['unblur_strength'] ?? null,
            'unblur_denoise' => (bool) ($options['unblur_denoise'] ?? true),
            'unblur_contrast' => (bool) ($options['unblur_contrast'] ?? true),
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

        $targetKb = ! empty($options['target_kb']) ? (float) $options['target_kb'] : null;
        $userQuality = ! empty($options['quality']) ? (int) $options['quality'] : null;
        $qualityUsed = null;

        if ($targetKb) {
            $targetBytes = (int) ($targetKb * 1024);
            $encode = function ($img, $q) use ($format) {
                ob_start();
                if ($format === 'jpg') {
                    imagejpeg($img, null, (int) $q);
                } elseif ($format === 'webp') {
                    imagewebp($img, null, (int) $q);
                } else {
                    imagepng($img, null, 9);
                }
                return ob_get_clean();
            };

            if ($format === 'png') {
                $curr = $canvas;
                $data = $encode($curr, 9);
                while (strlen($data) > $targetBytes && imagesx($curr) > 64 && imagesy($curr) > 64) {
                    $nw = max(32, (int) (imagesx($curr) * 0.82));
                    $nh = max(32, (int) (imagesy($curr) * 0.82));
                    $scaled = imagescale($curr, $nw, $nh, IMG_LANCZOS);
                    if ($curr !== $canvas) {
                        imagedestroy($curr);
                    }
                    $curr = $scaled;
                    $data = $encode($curr, 9);
                }
                file_put_contents($destination, $data);
                $finalW = imagesx($curr);
                $finalH = imagesy($curr);
                if ($curr !== $canvas) {
                    imagedestroy($curr);
                }
            } else {
                $curr = $canvas;
                $minQ = 12;
                $maxQ = 95;
                $bestData = null;
                $bestQ = $minQ;

                for ($iter = 0; $iter < 12; $iter++) {
                    $minData = $encode($curr, $minQ);
                    if (strlen($minData) <= $targetBytes) {
                        $l = $minQ;
                        $r = $maxQ;
                        while ($l <= $r) {
                            $mid = (int) (($l + $r) / 2);
                            $data = $encode($curr, $mid);
                            if (strlen($data) <= $targetBytes) {
                                $bestData = $data;
                                $bestQ = $mid;
                                $l = $mid + 1;
                            } else {
                                $r = $mid - 1;
                            }
                        }
                        if ($bestData !== null) {
                            break;
                        }
                    }

                    $cw = imagesx($curr);
                    $ch = imagesy($curr);
                    if ($cw <= 80 || $ch <= 80) {
                        $bestData = $minData;
                        $bestQ = $minQ;
                        break;
                    }

                    $nw = max(60, (int) ($cw * 0.82));
                    $nh = max(60, (int) ($ch * 0.82));
                    $scaled = imagescale($curr, $nw, $nh, IMG_LANCZOS);
                    if ($curr !== $canvas) {
                        imagedestroy($curr);
                    }
                    $curr = $scaled;
                }

                if ($bestData === null) {
                    $bestData = $encode($curr, $minQ);
                    $bestQ = $minQ;
                }

                file_put_contents($destination, $bestData);
                $finalW = imagesx($curr);
                $finalH = imagesy($curr);
                $qualityUsed = $bestQ;
                if ($curr !== $canvas) {
                    imagedestroy($curr);
                }
            }
        } else {
            $finalW = $newW;
            $finalH = $newH;
            if ($format === 'jpg') {
                $qualityUsed = $userQuality ? max(5, min(100, $userQuality)) : 92;
                imagejpeg($canvas, $destination, $qualityUsed);
            } elseif ($format === 'webp') {
                $qualityUsed = $userQuality ? max(5, min(100, $userQuality)) : 88;
                imagewebp($canvas, $destination, $qualityUsed);
            } else {
                imagepng($canvas, $destination, 6);
            }
        }
        imagedestroy($canvas);

        return [
            'path' => $destination,
            'width' => $finalW,
            'height' => $finalH,
            'format' => $format,
            'size_bytes' => @filesize($destination) ?: 0,
            'quality_used' => $qualityUsed,
            'target_kb' => $targetKb,
            'background' => $bgOption,
            'background_asset' => null,
            'background_removed' => false,
        ];
    }
}

