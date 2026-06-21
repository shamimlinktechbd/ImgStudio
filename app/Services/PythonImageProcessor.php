<?php

namespace App\Services;

use RuntimeException;

class PythonImageProcessor
{
    public function process(string $source, string $destination, array $options): array
    {
        $python = config('services.python.binary', 'python');
        $script = base_path('python-service/processor.py');

        $payload = json_encode([
            'source' => $source,
            'destination' => $destination,
            'format' => $options['format'] ?? 'png',
            'width' => $options['width'] ?? null,
            'height' => $options['height'] ?? null,
            'background' => $options['background'] ?? 'original',
            'remove_background' => (bool) ($options['remove_background'] ?? false),
            'background_path' => $options['background_path'] ?? null,
        ]);

        $command = implode(' ', [
            escapeshellarg($python),
            escapeshellarg($script),
            escapeshellarg($payload),
        ]);

        $output = [];
        $exitCode = 0;
        exec($command . ' 2>&1', $output, $exitCode);

        if ($exitCode !== 0) {
            throw new RuntimeException('Python image processor failed: ' . implode("\n", $output));
        }

        $result = json_decode(implode("\n", $output), true);

        if (! is_array($result)) {
            throw new RuntimeException('Python image processor returned invalid JSON.');
        }

        return $result;
    }
}
