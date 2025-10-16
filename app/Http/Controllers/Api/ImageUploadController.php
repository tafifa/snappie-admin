<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ImageUploadController extends Controller
{
    protected CloudinaryService $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->cloudinaryService = $cloudinaryService;
    }

    /**
     * Upload single file (image or video)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function upload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:jpeg,png,jpg,gif,webp,mp4,mov,avi,wmv,webm|max:51200', // max 50MB
            'folder' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $folder = $request->input('folder', 'snappie/uploads');
            
            $result = $this->cloudinaryService->upload(
                $request->file('file'),
                $folder
            );

            $fileType = $result['resource_type'] === 'video' ? 'Video' : 'Image';

            return response()->json([
                'success' => true,
                'message' => "{$fileType} uploaded successfully",
                'data' => $result,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload multiple files (images and/or videos) - max 5 files
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadMultiple(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'files' => 'required|array|min:1|max:5',
            'files.*' => 'required|file|mimes:jpeg,png,jpg,gif,webp,mp4,mov,avi,wmv,webm|max:51200', // max 50MB per file
            'folder' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $folder = $request->input('folder', 'snappie/uploads');
            $files = $request->file('files');

            $result = $this->cloudinaryService->uploadMultiple($files, $folder, 5);

            $message = "Uploaded {$result['total_uploaded']} file(s) successfully";
            if ($result['total_failed'] > 0) {
                $message .= ", {$result['total_failed']} failed";
            }

            return response()->json([
                'success' => $result['total_failed'] === 0,
                'message' => $message,
                'data' => $result,
            ], $result['total_failed'] === 0 ? 200 : 207); // 207 Multi-Status for partial success
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete gambar dari Cloudinary
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'public_id' => 'required_without:url|string',
            'url' => 'required_without:public_id|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $deleted = false;

            if ($request->has('public_id')) {
                $deleted = $this->cloudinaryService->delete($request->input('public_id'));
            } elseif ($request->has('url')) {
                $deleted = $this->cloudinaryService->deleteByUrl($request->input('url'));
            }

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Image deleted successfully',
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
