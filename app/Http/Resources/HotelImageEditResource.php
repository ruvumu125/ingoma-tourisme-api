<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class HotelImageEditResource extends JsonResource
{
    public function toArray($request): array
    {
        // Get the relative path from the database
        $relativePath = $this->image_url;

        // Generate the public URL
        $imagePath = asset('storage/' . $relativePath);

        // Full storage path to the file
        $fullPath = storage_path('app/public/' . $relativePath);

        // Check if the file exists
        if (file_exists($fullPath)) {
            // Get the file content and encode it to Base64
            $imageData = base64_encode(file_get_contents($fullPath));
            $mimeType = mime_content_type($fullPath); // Dynamically get MIME type
            $imageUrl = 'data:' . $mimeType . ';base64,' . $imageData;
        } else {
            // Fallback if the file doesn't exist
            $imageUrl = null;
        }

        return [
            'id' => $this->id,
            'image_url' => $imageUrl, // Base64-encoded image
            'image_path' => $imagePath, // Publicly accessible URL
            'is_main' => $this->is_main,
            'description' => $this->description,
        ];
    }
}
