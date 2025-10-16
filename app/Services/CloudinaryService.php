<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class CloudinaryService
{
    protected Cloudinary $cloudinary;

    public function __construct()
    {
        // Check if CLOUDINARY_URL is set (format: cloudinary://api_key:api_secret@cloud_name)
        $cloudinaryUrl = env('CLOUDINARY_URL');

        if ($cloudinaryUrl) {
            // Parse CLOUDINARY_URL
            $this->cloudinary = new Cloudinary($cloudinaryUrl);
        } else {
            // Use individual credentials
            $this->cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => config('services.cloudinary.cloud_name'),
                    'api_key' => config('services.cloudinary.api_key'),
                    'api_secret' => config('services.cloudinary.api_secret'),
                ],
                'url' => [
                    'secure' => true
                ]
            ]);
        }
    }

    /**
     * Upload file ke Cloudinary (support image & video)
     *
     * @param UploadedFile|string $file File yang akan diupload
     * @param string $folder Folder tujuan di Cloudinary
     * @param array $options Opsi tambahan untuk upload
     * @return array ['url' => string, 'public_id' => string, 'secure_url' => string]
     * @throws \Exception
     */
    public function upload($file, string $folder, array $options = []): array
    {
        try {
            // Jika file adalah UploadedFile, ambil path-nya
            $filePath = $file instanceof UploadedFile ? $file->getRealPath() : $file;

            // Detect file type
            $mimeType = $file instanceof UploadedFile ? $file->getMimeType() : mime_content_type($filePath);
            $isVideo = str_starts_with($mimeType, 'video/');

            // Default options
            $defaultOptions = [
                'folder' => $folder,
                'resource_type' => 'auto',
                'overwrite' => false,
                'invalidate' => true,
            ];

            // Add transformation for images only
            if (!$isVideo) {
                $defaultOptions['transformation'] = [
                    'width' => 1920,
                    'height' => 1080,
                    'crop' => 'limit',
                    'quality' => 'auto:good',
                    'fetch_format' => 'auto',
                ];
            } else {
                // Basic video optimization
                $defaultOptions['transformation'] = [
                    'quality' => 'auto',
                    'fetch_format' => 'auto',
                ];
            }

            // Merge with custom options
            $uploadOptions = array_merge($defaultOptions, $options);

            // Upload ke Cloudinary
            $result = $this->cloudinary->uploadApi()->upload($filePath, $uploadOptions);

            return [
                'url' => $result['url'] ?? null,
                'secure_url' => $result['secure_url'] ?? null,
                'public_id' => $result['public_id'] ?? null,
                'format' => $result['format'] ?? null,
                'resource_type' => $result['resource_type'] ?? null,
                'width' => $result['width'] ?? null,
                'height' => $result['height'] ?? null,
                'bytes' => $result['bytes'] ?? null,
                'duration' => $result['duration'] ?? null, // for videos
            ];
        } catch (\Exception $e) {
            Log::error('Cloudinary upload error: ' . $e->getMessage());
            throw new \Exception('Failed to upload file to Cloudinary: ' . $e->getMessage());
        }
    }

    /**
     * Upload multiple files ke Cloudinary
     *
     * @param array $files Array of UploadedFile
     * @param string $folder Folder tujuan di Cloudinary
     * @param int $maxFiles Maximum number of files (default: 5)
     * @return array Array of upload results
     * @throws \Exception
     */
    public function uploadMultiple(array $files, string $folder, int $maxFiles = 5): array
    {
        // Limit to max files
        if (count($files) > $maxFiles) {
            throw new \Exception("Maximum {$maxFiles} files allowed");
        }

        $results = [];
        $errors = [];

        foreach ($files as $index => $file) {
            try {
                $results[] = $this->upload($file, $folder);
            } catch (\Exception $e) {
                $errors[] = [
                    'index' => $index,
                    'filename' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'success' => $results,
            'errors' => $errors,
            'total_uploaded' => count($results),
            'total_failed' => count($errors),
        ];
    }

    /**
     * Upload thumbnail/avatar (square, cropped) - images only
     *
     * @param UploadedFile|string $file
     * @param string $folder
     * @param int $size Size of square (default: 400)
     * @return array
     * @throws \Exception
     */
    public function uploadThumbnail($file, string $folder, int $size = 400): array
    {
        $options = [
            'transformation' => [
                'width' => $size,
                'height' => $size,
                'crop' => 'fill',
                'gravity' => 'face',
                'quality' => 'auto',
                'fetch_format' => 'auto',
            ]
        ];

        return $this->upload($file, $folder, $options);
    }

    /**
     * Delete file dari Cloudinary berdasarkan public_id
     *
     * @param string $publicId Public ID dari file
     * @return bool
     */
    public function delete(string $publicId): bool
    {
        try {
            $result = $this->cloudinary->uploadApi()->destroy($publicId);
            return $result['result'] === 'ok';
        } catch (\Exception $e) {
            Log::error('Cloudinary delete error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete file berdasarkan URL
     *
     * @param string $url URL lengkap dari Cloudinary
     * @return bool
     */
    public function deleteByUrl(string $url): bool
    {
        $publicId = $this->extractPublicIdFromUrl($url);
        
        if (!$publicId) {
            return false;
        }

        return $this->delete($publicId);
    }

    /**
     * Extract public_id dari Cloudinary URL
     *
     * @param string $url
     * @return string|null
     */
    protected function extractPublicIdFromUrl(string $url): ?string
    {
        // URL format: https://res.cloudinary.com/{cloud_name}/{resource_type}/upload/{version}/{public_id}.{format}
        $pattern = '/\/upload\/(?:v\d+\/)?(.+)\.\w+$/';
        
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Get optimized URL dengan transformasi
     *
     * @param string $publicId
     * @param array $transformations
     * @return string
     */
    public function getOptimizedUrl(string $publicId, array $transformations = []): string
    {
        return $this->cloudinary->image($publicId)
            ->addTransformation($transformations)
            ->toUrl();
    }

    /**
     * Get thumbnail URL
     *
     * @param string $publicId
     * @param int $size
     * @return string
     */
    public function getThumbnailUrl(string $publicId, int $size = 200): string
    {
        return $this->getOptimizedUrl($publicId, [
            'width' => $size,
            'height' => $size,
            'crop' => 'fill',
            'gravity' => 'face',
        ]);
    }
}
