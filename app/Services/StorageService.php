<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class StorageService
{
    /**
     * Upload file to storage
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param string|null $filename
     * @return array
     * @throws Exception
     */
    public function uploadFile(UploadedFile $file, string $directory = 'uploads', ?string $filename = null): array
    {
        try {
            // Generate filename if not provided
            if (!$filename) {
                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            }

            // Store file
            $path = $file->storeAs($directory, $filename, 'public');

            return [
                'success' => true,
                'path' => $path,
                'url' => Storage::url($path),
                'filename' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType()
            ];
        } catch (Exception $e) {
            throw new Exception('Failed to upload file: ' . $e->getMessage());
        }
    }

    /**
     * Upload multiple files
     *
     * @param array $files
     * @param string $directory
     * @return array
     */
    public function uploadMultipleFiles(array $files, string $directory = 'uploads'): array
    {
        $results = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                try {
                    $results[] = $this->uploadFile($file, $directory);
                } catch (Exception $e) {
                    $results[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'filename' => $file->getClientOriginalName()
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * Upload image with validation
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param int $maxSize Maximum size in KB
     * @return array
     * @throws Exception
     */
    public function uploadImage(UploadedFile $file, string $directory = 'images', int $maxSize = 2048): array
    {
        // Validate image
        if (!in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
            throw new Exception('Invalid image format. Only JPEG, PNG, GIF, and WebP are allowed.');
        }

        // Check file size (in KB)
        if ($file->getSize() > ($maxSize * 1024)) {
            throw new Exception("Image size must be less than {$maxSize}KB.");
        }

        return $this->uploadFile($file, $directory);
    }

    /**
     * Delete file from storage
     *
     * @param string $path
     * @return bool
     */
    public function deleteFile(string $path): bool
    {
        try {
            return Storage::disk('public')->delete($path);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Delete multiple files
     *
     * @param array $paths
     * @return array
     */
    public function deleteMultipleFiles(array $paths): array
    {
        $results = [];

        foreach ($paths as $path) {
            $results[$path] = $this->deleteFile($path);
        }

        return $results;
    }

    /**
     * Check if file exists
     *
     * @param string $path
     * @return bool
     */
    public function fileExists(string $path): bool
    {
        return Storage::disk('public')->exists($path);
    }

    /**
     * Get file URL
     *
     * @param string $path
     * @return string|null
     */
    public function getFileUrl(string $path): ?string
    {
        if ($this->fileExists($path)) {
            return Storage::url($path);
        }

        return null;
    }

    /**
     * Get file size
     *
     * @param string $path
     * @return int|null
     */
    public function getFileSize(string $path): ?int
    {
        try {
            return Storage::disk('public')->size($path);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Move file to different directory
     *
     * @param string $fromPath
     * @param string $toPath
     * @return bool
     */
    public function moveFile(string $fromPath, string $toPath): bool
    {
        try {
            return Storage::disk('public')->move($fromPath, $toPath);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Copy file to different location
     *
     * @param string $fromPath
     * @param string $toPath
     * @return bool
     */
    public function copyFile(string $fromPath, string $toPath): bool
    {
        try {
            return Storage::disk('public')->copy($fromPath, $toPath);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get all files in directory
     *
     * @param string $directory
     * @return array
     */
    public function getFilesInDirectory(string $directory): array
    {
        try {
            return Storage::disk('public')->files($directory);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Create directory if not exists
     *
     * @param string $directory
     * @return bool
     */
    public function createDirectory(string $directory): bool
    {
        try {
            if (!Storage::disk('public')->exists($directory)) {
                return Storage::disk('public')->makeDirectory($directory);
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Delete directory and all contents
     *
     * @param string $directory
     * @return bool
     */
    public function deleteDirectory(string $directory): bool
    {
        try {
            return Storage::disk('public')->deleteDirectory($directory);
        } catch (Exception $e) {
            return false;
        }
    }
}