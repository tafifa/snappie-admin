<?php

namespace App\Observers;

use App\Models\Place;
use App\Services\CloudinaryService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PlaceObserver
{
    protected CloudinaryService $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->cloudinaryService = $cloudinaryService;
    }

    /**
     * Handle the Place "saving" event.
     * This runs before creating and updating.
     */
    public function saving(Place $place): void
    {
        $this->handleImageUpload($place);
        $this->handleMenuImageUpload($place);
    }

    /**
     * Handle the Place "deleted" event.
     */
    public function deleted(Place $place): void
    {
        // Delete all images from Cloudinary
        if ($place->image_urls && is_array($place->image_urls)) {
            foreach ($place->image_urls as $imageUrl) {
                if (str_contains($imageUrl, 'cloudinary.com')) {
                    try {
                        $this->cloudinaryService->deleteByUrl($imageUrl);
                    } catch (\Exception $e) {
                        Log::warning("Failed to delete Cloudinary image for place {$place->id}: " . $e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * Handle multiple images upload to Cloudinary
     */
    protected function handleImageUpload(Place $place): void
    {
        // Check if image_urls is set and has changed
        $imageUrls = $place->getAttributes()['image_urls'] ?? null;

        if (!$imageUrls || !$place->isDirty('image_urls')) {
            return;
        }

        // Decode JSON if needed
        if (is_string($imageUrls)) {
            $imageUrls = json_decode($imageUrls, true);
        }

        if (!is_array($imageUrls) || empty($imageUrls)) {
            return;
        }

        $cloudinaryUrls = [];
        $hasLocalFiles = false;

        foreach ($imageUrls as $imageUrl) {
            $cloudinaryUrls[] = $this->uploadIfNeeded($imageUrl, 'places', $hasLocalFiles);
        }

        if ($hasLocalFiles) {
            $place->setAttribute('image_urls', $cloudinaryUrls);
        }
    }

    /**
     * Ensure menu images that live inside additional_info are uploaded as well.
     */
    protected function handleMenuImageUpload(Place $place): void
    {
        $additionalInfo = $place->getAttributes()['additional_info'] ?? null;

        if (!$additionalInfo) {
            return;
        }

        if (is_string($additionalInfo)) {
            $additionalInfo = json_decode($additionalInfo, true);
        }

        if (!is_array($additionalInfo) || empty($additionalInfo)) {
            return;
        }

        $hasLocalFiles = false;

        if (!empty($additionalInfo['menu_image_url'])) {
            $additionalInfo['menu_image_url'] = $this->uploadIfNeeded(
                $additionalInfo['menu_image_url'],
                'places',
                $hasLocalFiles
            );
        }

        if (!empty($additionalInfo['menu']) && is_array($additionalInfo['menu'])) {
            foreach ($additionalInfo['menu'] as $index => $menuItem) {
                if (empty($menuItem['image_url'])) {
                    continue;
                }

                $additionalInfo['menu'][$index]['image_url'] = $this->uploadIfNeeded(
                    $menuItem['image_url'],
                    'places',
                    $hasLocalFiles
                );
            }
        }

        if ($hasLocalFiles) {
            $place->setAttribute('additional_info', $additionalInfo);
        }
    }

    /**
     * Upload a local file path to Cloudinary if required and return its final URL.
     */
    protected function uploadIfNeeded(?string $path, string $folder, bool &$hasLocalFiles): ?string
    {
        if (!$path) {
            return $path;
        }

        if (str_contains($path, 'cloudinary.com') || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $hasLocalFiles = true;

        try {
            $fullPath = Storage::disk('public')->path($path);

            if (file_exists($fullPath)) {
                $result = $this->cloudinaryService->upload($fullPath, $folder);
                Storage::disk('public')->delete($path);
                Log::info("Uploaded place image to Cloudinary: {$result['secure_url']}");

                return $result['secure_url'];
            }

            Log::warning("Place image file not found: {$fullPath}");
            return $path;
        } catch (\Exception $e) {
            Log::error("Failed to upload place image to Cloudinary: " . $e->getMessage());
            return $path;
        }
    }
}
