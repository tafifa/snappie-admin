<?php

namespace Tests\Feature;

use App\Services\CloudinaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CloudinaryTest extends TestCase
{
    protected CloudinaryService $cloudinary;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cloudinary = app(CloudinaryService::class);
    }

    /**
     * Test if Cloudinary configuration is valid
     */
    public function test_cloudinary_configuration_is_valid(): void
    {
        $cloudinaryUrl = env('CLOUDINARY_URL');
        $cloudName = config('services.cloudinary.cloud_name');

        $this->assertTrue(
            !empty($cloudinaryUrl) || !empty($cloudName),
            'Cloudinary credentials not found. Please set CLOUDINARY_URL or individual credentials in .env'
        );
    }

    /**
     * Test if CLOUDINARY_URL format is correct
     */
    public function test_cloudinary_url_format(): void
    {
        $cloudinaryUrl = env('CLOUDINARY_URL');
        
        if ($cloudinaryUrl) {
            $this->assertMatchesRegularExpression(
                '/^cloudinary:\/\/\d+:[^@]+@.+$/',
                $cloudinaryUrl,
                'CLOUDINARY_URL format is invalid. Expected format: cloudinary://api_key:api_secret@cloud_name'
            );
        } else {
            $this->markTestSkipped('CLOUDINARY_URL not set, using individual credentials');
        }
    }

    /**
     * Test basic file upload to Cloudinary
     */
    public function test_can_upload_file_to_cloudinary(): void
    {
        // Create a test file
        $testFile = UploadedFile::fake()->image('test-upload.jpg', 100, 100);

        // Upload to Cloudinary
        $result = $this->cloudinary->upload($testFile, 'snappie/test');

        // Assertions
        $this->assertIsArray($result);
        $this->assertArrayHasKey('secure_url', $result);
        $this->assertArrayHasKey('public_id', $result);
        $this->assertArrayHasKey('resource_type', $result);
        $this->assertNotNull($result['secure_url']);
        $this->assertNotNull($result['public_id']);
        $this->assertStringContainsString('cloudinary.com', $result['secure_url']);
        $this->assertStringStartsWith('snappie/test/', $result['public_id']);
        $this->assertEquals('image', $result['resource_type']);

        // Cleanup
        $this->cloudinary->delete($result['public_id']);
    }

    /**
     * Test video upload to Cloudinary
     */
    public function test_can_upload_video_to_cloudinary(): void
    {
        // Create a test video file
        $testFile = UploadedFile::fake()->create('test-video.mp4', 1000, 'video/mp4');

        // Upload to Cloudinary
        $result = $this->cloudinary->upload($testFile, 'snappie/test');

        // Assertions
        $this->assertIsArray($result);
        $this->assertArrayHasKey('secure_url', $result);
        $this->assertArrayHasKey('public_id', $result);
        $this->assertArrayHasKey('resource_type', $result);
        $this->assertNotNull($result['secure_url']);
        $this->assertNotNull($result['public_id']);
        $this->assertEquals('video', $result['resource_type']);

        // Cleanup
        $this->cloudinary->delete($result['public_id']);
    }

    /**
     * Test multiple files upload
     */
    public function test_can_upload_multiple_files(): void
    {
        // Create test files (mix of images and videos)
        $files = [
            UploadedFile::fake()->image('test1.jpg', 100, 100),
            UploadedFile::fake()->image('test2.png', 200, 200),
            UploadedFile::fake()->create('test3.mp4', 500, 'video/mp4'),
        ];

        // Upload multiple files
        $result = $this->cloudinary->uploadMultiple($files, 'snappie/test', 5);

        // Assertions
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('total_uploaded', $result);
        $this->assertArrayHasKey('total_failed', $result);
        
        $this->assertEquals(3, $result['total_uploaded']);
        $this->assertEquals(0, $result['total_failed']);
        $this->assertCount(3, $result['success']);

        // Cleanup all uploaded files
        foreach ($result['success'] as $upload) {
            $this->cloudinary->delete($upload['public_id']);
        }
    }

    /**
     * Test multiple files with limit exceeded
     */
    public function test_multiple_upload_exceeds_limit(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Maximum 5 files allowed');

        // Create 6 files (exceeds limit)
        $files = [];
        for ($i = 0; $i < 6; $i++) {
            $files[] = UploadedFile::fake()->image("test{$i}.jpg", 100, 100);
        }

        // Should throw exception
        $this->cloudinary->uploadMultiple($files, 'snappie/test', 5);
    }

    /**
     * Test optimized upload (deprecated method - will be removed)
     */
    public function test_can_upload_optimized_image(): void
    {
        $testFile = UploadedFile::fake()->image('test-optimized.jpg', 2000, 2000);

        // Use basic upload (already optimized by default)
        $result = $this->cloudinary->upload($testFile, 'snappie/test');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('secure_url', $result);
        $this->assertArrayHasKey('public_id', $result);
        $this->assertArrayHasKey('width', $result);
        $this->assertArrayHasKey('height', $result);
        
        // Check if width/height were limited (if provided by Cloudinary)
        if ($result['width'] && $result['height']) {
            $this->assertLessThanOrEqual(1920, $result['width']);
            $this->assertLessThanOrEqual(1080, $result['height']);
        }

        // Cleanup
        $this->cloudinary->delete($result['public_id']);
    }

    /**
     * Test thumbnail upload (square, cropped)
     */
    public function test_can_upload_thumbnail(): void
    {
        $testFile = UploadedFile::fake()->image('test-thumbnail.jpg', 800, 800);

        $result = $this->cloudinary->uploadThumbnail(
            $testFile,
            'snappie/test/avatars',
            400
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('secure_url', $result);
        $this->assertArrayHasKey('public_id', $result);
        $this->assertStringStartsWith('snappie/test/avatars/', $result['public_id']);

        // Cleanup
        $this->cloudinary->delete($result['public_id']);
    }

    /**
     * Test delete functionality
     */
    public function test_can_delete_file_from_cloudinary(): void
    {
        // First upload a test file
        $testFile = UploadedFile::fake()->image('test-delete.jpg', 100, 100);
        $result = $this->cloudinary->upload($testFile, 'snappie/test');

        $publicId = $result['public_id'];
        $this->assertNotNull($publicId);

        // Delete the file
        $deleted = $this->cloudinary->delete($publicId);

        $this->assertTrue($deleted, 'Failed to delete file from Cloudinary');
    }

    /**
     * Test delete by URL
     */
    public function test_can_delete_by_url(): void
    {
        // Upload test file
        $testFile = UploadedFile::fake()->image('test-delete-url.jpg', 100, 100);
        $result = $this->cloudinary->upload($testFile, 'snappie/test');

        $url = $result['secure_url'];
        $this->assertNotNull($url);

        // Delete by URL
        $deleted = $this->cloudinary->deleteByUrl($url);

        $this->assertTrue($deleted, 'Failed to delete file by URL');
    }

    /**
     * Test handling of invalid file
     */
    public function test_upload_fails_with_invalid_file(): void
    {
        $this->expectException(\Exception::class);

        // Try to upload non-existent file
        $this->cloudinary->upload('/path/to/nonexistent/file.jpg', 'snappie/test');
    }

    /**
     * Test get optimized URL
     */
    public function test_can_get_optimized_url(): void
    {
        // Upload test file first
        $testFile = UploadedFile::fake()->image('test-url.jpg', 800, 600);
        $result = $this->cloudinary->upload($testFile, 'snappie/test');

        $publicId = $result['public_id'];

        // Get optimized URL
        $url = $this->cloudinary->getOptimizedUrl($publicId, [
            'width' => 400,
            'height' => 300,
            'crop' => 'fill',
        ]);

        $this->assertIsString($url);
        $this->assertStringContainsString('cloudinary.com', $url);
        $this->assertStringContainsString($publicId, $url);

        // Cleanup
        $this->cloudinary->delete($publicId);
    }

    /**
     * Test get thumbnail URL
     */
    public function test_can_get_thumbnail_url(): void
    {
        // Upload test file
        $testFile = UploadedFile::fake()->image('test-thumb-url.jpg', 800, 600);
        $result = $this->cloudinary->upload($testFile, 'snappie/test');

        $publicId = $result['public_id'];

        // Get thumbnail URL
        $thumbnailUrl = $this->cloudinary->getThumbnailUrl($publicId, 200);

        $this->assertIsString($thumbnailUrl);
        $this->assertStringContainsString('cloudinary.com', $thumbnailUrl);

        // Cleanup
        $this->cloudinary->delete($publicId);
    }
}
