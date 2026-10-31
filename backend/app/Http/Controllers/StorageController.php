<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StorageController extends Controller
{
    public function show(Request $request, string $path)
    {
        $candidatePaths = [
            storage_path('app/public/' . ltrim($path, '/')),
            public_path('storage/' . ltrim($path, '/')),
        ];

        $fullPath = null;
        foreach ($candidatePaths as $candidate) {
            if (is_file($candidate)) {
                $fullPath = $candidate;
                break;
            }
        }

        if ($fullPath === null) {
            abort(404);
        }

        $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
}
