<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class MediaUploader
{
    protected string $disk = 'public';
    
    /**
     * Upload a file (generic method, alias for uploadImage)
     */
    public function upload(
        UploadedFile $file,
        string $folder,
        array $options = []
    ): string {
        return $this->uploadImage($file, $folder, $options);
    }

    /**
     * Upload and process an image
     */
    public function uploadImage(
        UploadedFile $file,
        string $folder,
        array $options = []
    ): string {
        $options = array_merge([
            'width' => null,
            'height' => null,
            'quality' => 85,
            'format' => 'webp',
            'maintain_aspect' => true,
        ], $options);

        // Generate unique filename
        $filename = $this->generateFilename($file, $options['format']);
        $path = "{$folder}/{$filename}";

        // Process image if Intervention Image is available
        if (class_exists(Image::class) && ($options['width'] || $options['height'])) {
            $image = Image::read($file);
            
            // Resize if dimensions specified
            if ($options['width'] || $options['height']) {
                if ($options['maintain_aspect']) {
                    $image->scale($options['width'], $options['height']);
                } else {
                    $image->cover($options['width'], $options['height']);
                }
            }

            // Encode to format
            $encoded = $image->toWebp($options['quality']);
            
            // Store
            Storage::disk($this->disk)->put($path, $encoded);
        } else {
            // Simple upload without processing
            $path = $file->storeAs($folder, $filename, $this->disk);
        }

        return $path;
    }

    /**
     * Upload a logo image
     */
    public function uploadLogo(UploadedFile $file, int $restaurantId): string
    {
        return $this->uploadImage($file, "restaurants/{$restaurantId}/logo", [
            'width' => 400,
            'height' => 400,
            'maintain_aspect' => false,
        ]);
    }

    /**
     * Upload a banner image
     */
    public function uploadBanner(UploadedFile $file, int $restaurantId): string
    {
        return $this->uploadImage($file, "restaurants/{$restaurantId}/banner", [
            'width' => 1920,
            'height' => 600,
            'maintain_aspect' => false,
        ]);
    }

    /**
     * Upload a dish image
     */
    public function uploadDishImage(UploadedFile $file, int $restaurantId): string
    {
        return $this->uploadImage($file, "restaurants/{$restaurantId}/dishes", [
            'width' => 800,
            'height' => 600,
            'maintain_aspect' => false,
        ]);
    }

    /**
     * Upload a category image
     */
    public function uploadCategoryImage(UploadedFile $file, int $restaurantId): string
    {
        return $this->uploadImage($file, "restaurants/{$restaurantId}/categories", [
            'width' => 600,
            'height' => 400,
            'maintain_aspect' => false,
        ]);
    }

    /**
     * Upload an avatar
     */
    public function uploadAvatar(UploadedFile $file, int $userId): string
    {
        return $this->uploadImage($file, "users/{$userId}/avatar", [
            'width' => 256,
            'height' => 256,
            'maintain_aspect' => false,
        ]);
    }

    /**
     * Delete a file
     */
    public function delete(?string $path): bool
    {
        if (!$path) {
            return true;
        }

        return Storage::disk($this->disk)->delete($path);
    }

    /**
     * Delete multiple files
     */
    public function deleteMany(array $paths): void
    {
        foreach ($paths as $path) {
            $this->delete($path);
        }
    }

    /**
     * Replace a file (delete old, upload new)
     */
    public function replace(
        ?string $oldPath,
        UploadedFile $newFile,
        string $folder,
        array $options = []
    ): string {
        // Delete old file
        $this->delete($oldPath);

        // Upload new file
        return $this->uploadImage($newFile, $folder, $options);
    }

    /**
     * Generate unique filename
     */
    protected function generateFilename(UploadedFile $file, string $extension = null): string
    {
        $extension = $extension ?? $file->getClientOriginalExtension();
        $uuid = Str::uuid();
        
        return "{$uuid}.{$extension}";
    }

    /**
     * Get file URL
     */
    public function getUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return Storage::disk($this->disk)->url($path);
    }

    /**
     * Check if file exists
     */
    public function exists(?string $path): bool
    {
        if (!$path) {
            return false;
        }

        return Storage::disk($this->disk)->exists($path);
    }
}

