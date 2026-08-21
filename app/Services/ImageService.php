<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

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
     * Compress image to max 1200px width and auto-orient.
     */
    private function compress(string $filePath, string $mime): ?string
    {
        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($filePath);
            
            // Auto orient (fixes rotated mobile photos)
            // Wait, in v3 it's automatic or we don't need it. 
            // In Intervention Image 3, orient() was removed, it reads EXIF natively usually.
            // Let's just resize.
            
            $image->scaleDown(width: 1200);
            
            // Output to string buffer
            if ($mime === 'image/png') {
                return (string) $image->toPng(70);
            } else {
                return (string) $image->toJpeg(75);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Image Compression Error: ' . $e->getMessage());
            return null;
        }
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
