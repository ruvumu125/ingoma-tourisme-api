<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class StorageController extends Controller
{
    public function getFile($filename)
    {
        $filePath = 'public/hotel_images/' . $filename;

        if (Storage::exists($filePath)) {
            $file = Storage::get($filePath);
            $mimeType = Storage::mimeType($filePath);

            return response($file, 200)
                ->header('Content-Type', $mimeType)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, OPTIONS');
        }

        return response('File not found', 404);
    }
}

