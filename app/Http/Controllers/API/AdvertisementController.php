<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdvertisementResource;
use App\Models\Advertisement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdvertisementController extends BaseController
{
    // List all videos
    public function getAllAdvertisements(Request $request)
    {
        $perPage = $request->query('per_page', 10); // Default to 10 items per page
        $search = $request->query('search'); // Get the search query parameter

        // Build the query
        $query = Advertisement::query();

        // Apply search filter if the search parameter is provided
        if ($search) {
            $query->where('advertisement_url', 'LIKE', "%{$search}%")
                ->orWhere('company_name', 'LIKE', "%{$search}%");
        }

        // Order by created_at in descending order
        $query->orderBy('created_at', 'desc');

        // Get paginated results
        $videos = $query->paginate($perPage);

        // Return response
        return $this->sendResponse(
            [
                'advertisements' => AdvertisementResource::collection($videos),
                'pagination' => [
                    'total' => $videos->total(),
                    'count' => $videos->count(),
                    'per_page' => $videos->perPage(),
                    'current_page' => $videos->currentPage(),
                    'total_pages' => $videos->lastPage(),
                ],
            ],
            'Videos retrieved successfully.'
        );
    }

    // Store a new video
    public function addAdvertisement(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string',
            'advertisement_url' => 'required|string',
            'file' => 'required|file|image|mimes:jpeg,png,jpg,gif|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $imageFile = $request->file('file');

        // Generate a unique filename
        $imageName = time() . '_' . uniqid() . '.' . $imageFile->getClientOriginalExtension();

        // Define the destination path
        $destinationPath = resource_path('uploads/advertisement_images');

        // Ensure the directory exists
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        // Move the uploaded file
        $imageFile->move($destinationPath, $imageName);

        $video = Advertisement::create([
            'company_name' => $request->company_name,
            'advertisement_url' => $request->advertisement_url,
            'file_data' => $imageName,
            'is_active' => false,
        ]);

        return $this->sendResponse(new AdvertisementResource($video), 'Video uploaded successfully');
    }

    // Show a specific video
    public function getAdvertisementById($id)
    {
        $video = Advertisement::find($id);

        if (is_null($video)) {
            return $this->sendError('Video not found.');
        }
        return $this->sendResponse(new AdvertisementResource($video), 'Video retrieved successfully.');
    }

    public function getActiveAdvertisement()
    {
        // Fetch the first active video
        $activeVideo = Advertisement::where('is_active', true)->first();

        // Check if an active video exists
        if ($activeVideo) {

            return $this->sendResponse(new AdvertisementResource($activeVideo), 'Video retrieved successfully.');

        } else {

            return $this->sendError('Video not found.');
        }
    }


    // Update a video
    public function updateAdvertisement(Request $request, $id)
    {
        $video = Advertisement::find($id);

        if (is_null($video)) {
            return $this->sendError('Video not found.');
        }

        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string',
            'advertisement_url' => 'required|string',
            'file_data' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1000'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        if ($request->hasFile('file')) {

            $destinationPath = resource_path('uploads/advertisement_images');

            // Ensure directory exists
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            // Delete the old image if it exists
            if ($video->file_path && file_exists($destinationPath . '/' . basename($video->file_path))) {
                unlink($destinationPath . '/' . basename($video->file_path));
            }

            // Generate a new filename
            $newFileName = time() . '_' . uniqid() . '.' . $request->file('file')->getClientOriginalExtension();

            // Move the file
            $request->file('file')->move($destinationPath, $newFileName);

            // Update file path in DB
            $video->file_data = $newFileName;
        }
        $video->company_name = $request->get('company_name');
        $video->advertisement_url = $request->get('advertisement_url');
        $video->save();

        return $this->sendResponse(new AdvertisementResource($video), 'Video updated successfully.');
    }

    public function enableAdvertisement(Request $request, $id)
    {
        $video = Advertisement::find($id);
        if (is_null($video)) {
            return $this->sendError('Video not found.');
        }

        // Enable the specified video
        $video->is_active = 1;
        $video->save();

        return $this->sendResponse(new AdvertisementResource($video), 'Video enabled successfully.');
    }


    public function desableAdvertisement(Request $request, $id)
    {
        $video = Advertisement::find($id);
        if (is_null($video)) {
            return $this->sendError('User not found.');
        }

        $video->is_active =0;
        $video->save();

        return $this->sendResponse(new AdvertisementResource($video), 'Video  enabled successfully.');
    }



    // Delete a video
    public function deleteAdvertisement($id)
    {
        $video = Advertisement::find($id);

        if (is_null($video)) {
            return $this->sendError('Video not found.');
        }
        $video->delete();

        return $this->sendResponse([], 'Video deleted successfully.');
    }
}
