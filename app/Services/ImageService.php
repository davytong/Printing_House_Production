<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    /**
     * Store and compress an uploaded image.
     * Returns the stored path.
     */
    public function store(UploadedFile $file, string $directory = 'stock-reports'): string
    {
        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $filename  = 'report_' . now()->format('Ymd_His') . '_' . uniqid() . '.' . $extension;

        // Try to compress if it's an image
        if (in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'])) {
            $compressed = $this->compress($file->getRealPath(), $file->getMimeType());
            if ($compressed) {
                $path = $directory . '/' . $filename;
                Storage::disk('public')->put($path, $compressed);
                return $path;
            }
        }

        // Fallback: store as-is
        return $file->storeAs($directory, $filename, 'public');
    }

    /**
     * Compress image to max 1MB / 1200px width.
     */
    private function compress(string $filePath, string $mime): ?string
    {
        if (! extension_loaded('gd')) {
            return null;
        }

        $image = match($mime) {
            'image/jpeg' => @imagecreatefromjpeg($filePath),
            'image/png'  => @imagecreatefrompng($filePath),
            'image/webp' => @imagecreatefromwebp($filePath),
            default      => null,
        };

        if (! $image) return null;

        $width  = imagesx($image);
        $height = imagesy($image);

        // Resize if wider than 1200px
        $maxWidth = 1200;
        if ($width > $maxWidth) {
            $ratio     = $maxWidth / $width;
            $newHeight = (int) ($height * $ratio);
            $resized   = imagecreatetruecolor($maxWidth, $newHeight);

            // Preserve transparency for PNG
            if ($mime === 'image/png') {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
            }

            imagecopyresampled($resized, $image, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $resized;
        }

        // Output to buffer
        ob_start();
        if ($mime === 'image/png') {
            imagepng($image, null, 7); // compression 0-9
        } else {
            imagejpeg($image, null, 75); // quality 75%
        }
        $output = ob_get_clean();
        imagedestroy($image);

        return $output;
    }

    /**
     * Delete an image from storage.
     */
    public function delete(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
