<?php

namespace App\Observers;

use App\Models\Review;
use App\Services\CloudinaryService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ReviewObserver
{
    protected CloudinaryService $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->cloudinaryService = $cloudinaryService;
    }

    /**
     * Handle the Review "saving" event.
     * This runs before creating and updating.
     */
    public function saving(Review $review): void
    {
        $this->handleImageUpload($review);
    }

    /**
     * Handle the Review "deleted" event.
     */
    public function deleted(Review $review): void
    {
        // Delete all images from Cloudinary
        if ($review->image_urls && is_array($review->image_urls)) {
            foreach ($review->image_urls as $imageUrl) {
                if (str_contains($imageUrl, 'cloudinary.com')) {
                    try {
                        $this->cloudinaryService->deleteByUrl($imageUrl);
                    } catch (\Exception $e) {
                        Log::warning("Failed to delete Cloudinary image for review {$review->id}: " . $e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * Handle multiple images upload to Cloudinary
     */
    protected function handleImageUpload(Review $review): void
    {
        // Check if image_urls is set and has changed
        $imageUrls = $review->getAttributes()['image_urls'] ?? null;
        
        if (!$imageUrls || !$review->isDirty('image_urls')) {
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
            // Skip if already a Cloudinary URL
            if (str_contains($imageUrl, 'cloudinary.com')) {
                $cloudinaryUrls[] = $imageUrl;
                continue;
            }

            // Skip if it's an external URL
            if (str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://')) {
                $cloudinaryUrls[] = $imageUrl;
                continue;
            }

            // It's a local path, needs to be uploaded
            $hasLocalFiles = true;
            
            try {
                $fullPath = Storage::disk('public')->path($imageUrl);
                
                if (file_exists($fullPath)) {
                    // Upload to Cloudinary
                    $result = $this->cloudinaryService->upload($fullPath, 'reviews');
                    $cloudinaryUrls[] = $result['secure_url'];
                    
                    // Delete the local temporary file
                    Storage::disk('public')->delete($imageUrl);
                    
                    Log::info("Uploaded review image to Cloudinary: {$result['secure_url']}");
                } else {
                    // File doesn't exist, keep original value
                    $cloudinaryUrls[] = $imageUrl;
                    Log::warning("Review image file not found: {$fullPath}");
                }
            } catch (\Exception $e) {
                Log::error("Failed to upload review image to Cloudinary: " . $e->getMessage());
                // Keep the original path if upload fails
                $cloudinaryUrls[] = $imageUrl;
            }
        }

        // Update with Cloudinary URLs only if there were local files to upload
        if ($hasLocalFiles) {
            $review->setAttribute('image_urls', json_encode($cloudinaryUrls));
        }
    }
}
