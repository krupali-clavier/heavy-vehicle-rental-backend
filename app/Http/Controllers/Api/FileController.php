<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FileService;
use Illuminate\Http\Request;
use Throwable;

class FileController extends Controller
{
    protected FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function uploadFile(Request $request)
    {
        try {
            $request->validate([
                'document_type' => 'required',
                'type' => 'required',
                'file' => 'required|file|max:10240',
            ]);

            $file = $request->file('file');
            if ($file && $file->isValid()) {
                $data = $this->fileService->uploadFile($file, $request->type, $request->document_type);

                return response()->json(['message' => 'File uploaded successfully', 'data' => $data], 200);
            }

            return response()->json(['message' => 'File not found'], 422);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
