<?php
namespace App\Http\Controllers;

use App\Models\ImageSave;
use App\Services\ImageUploadService;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageUploadController extends Controller
{
    protected $imageUploadService;

    public function __construct(ImageUploadService $imageUploadService)
    {
        $this->imageUploadService = $imageUploadService;
    }
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);

        $file = $request->file('image');

        $result = $this->imageUploadService->uploadImageToGCS($file);

        return response()->json([
            'message'    => 'File uploaded and saved successfully!',
            'gsutil_uri' => $result['gsutil_uri'],
            'image_id'   => $result['image_id'],
        ]);
    }

    public function getImage($imageId)
    {
        $publicUrl = $this->imageUploadService->getImageUrl($imageId);

        if (! $publicUrl) {
            return response()->json(['message' => 'Image not found'], 404);
        }

        return response()->json([
            'message'    => 'Image retrieved successfully!',
            'public_url' => $publicUrl,
        ]);
    }
    public function deleteImage($imageId)
    {
        // Find the image in the database
        $image = ImageSave::find($imageId);

        if (! $image) {
            return response()->json(['message' => 'Image not found in database'], 404);
        }

        // Delete the image from Google Cloud Storage
        $deleted = $this->imageUploadService->deleteImage($imageId);

        if (! $deleted) {
            return response()->json(['message' => 'Failed to delete image from Cloud Storage'], 500);
        }

        // Now delete the record from the database
        $image->delete();

        return response()->json(['message' => 'Image deleted successfully from Cloud Storage and database!']);
    }

    public function updateImage(Request $request, $imageId)
    {
        // Validate the incoming request to ensure the 'image' field is a valid image
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:2048',  // 2MB max
            'removeDoc' => 'required|string',  // The old image URL to remove
        ]);

        // Retrieve the 'removeDoc' URL (old image URL) and 'image' (new file)
        $removeDoc = $request->input('removeDoc');  // The old image URL (gs://...)
        $newFile = $request->file('image');  // The new image file uploaded

        // Call the ImageUploadService to handle the image update logic
        $result = $this->imageUploadService->updateImage($imageId, $removeDoc, $newFile);

        if (!$result) {
            return response()->json(['message' => 'Image not found'], 404);
        }

        // Return a success response with the details
        return response()->json([
            'message'    => $result['message'],
            'gsutil_uri' => $result['gsutil_uri'],
            'image_id'   => $result['image_id'],
        ]);
    }


}
