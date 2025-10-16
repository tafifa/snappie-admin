<?php

namespace App\Observers;

use App\Models\Checkin;
use App\Services\CloudinaryService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CheckinObserver
{
    protected CloudinaryService $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->cloudinaryService = $cloudinaryService;
    }

    /**
     * Handle the Checkin "saving" event.
     * This runs before creating and updating.
     */
    public function saving(Checkin $checkin): void
    {
        $this->handleImageUpload($checkin);
    }

    /**
     * Handle the Checkin "deleted" event.
     */
    public function deleted(Checkin $checkin): void
    {
        // Delete from Cloudinary if it's a Cloudinary URL
        if ($checkin->image_url && str_contains($checkin->image_url, 'cloudinary.com')) {
            try {
                $this->cloudinaryService->deleteByUrl($checkin->image_url);
            } catch (\Exception $e) {
                Log::warning("Failed to delete Cloudinary image for checkin {$checkin->id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Handle image upload to Cloudinary
     */
    protected function handleImageUpload(Checkin $checkin): void
    {
        // Check if image_url is set and it's a local path (not a Cloudinary URL)
        $imageUrl = $checkin->getAttributes()['image_url'] ?? null;
        
        if (!$imageUrl || !$checkin->isDirty('image_url')) {
            return;
        }
        
        // Skip if already a Cloudinary URL
        if (str_contains($imageUrl, 'cloudinary.com')) {
            return;
        }

        // Skip if it's an external URL
        if (str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://')) {
            return;
        }

        try {
            // It's a local path, upload to Cloudinary
            $fullPath = Storage::disk('public')->path($imageUrl);
            
            if (file_exists($fullPath)) {
                // Upload to Cloudinary
                $result = $this->cloudinaryService->upload($fullPath, 'checkins');
                
                // Update with Cloudinary URL (using setAttribute to avoid accessor issues)
                $checkin->setAttribute('image_url', $result['secure_url']);
                
                // Delete the local temporary file
                Storage::disk('public')->delete($imageUrl);
                
                Log::info("Uploaded checkin image to Cloudinary: {$result['secure_url']}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to upload checkin image to Cloudinary: " . $e->getMessage());
            // Don't throw exception, let the record save with local path
        }
    }
}
