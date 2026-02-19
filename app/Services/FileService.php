<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileService
{
    public function uploadFile(UploadedFile $file, string $type, ?string $documentType = null)
    {
        $extension = $file->getClientOriginalExtension();
        $fileName = uniqid() . '.' . $extension;
        $path = $type . '/' . $fileName;

        if (Storage::disk(config('app.disk', 'public'))->put($path, file_get_contents($file))) {
            $record = [
                'type' => $type,
                'document_type' => $documentType ?? $extension,
                'url' => Storage::disk(config('app.disk', 'public'))->url($path),
                'path' => $path,
                'name' => $fileName,
            ];
            return Document::create($record);
        }
        return null;
    }

    public function deleteFile($filePath)
    {
        if ($filePath) {
            return Storage::disk(config('app.disk', 'public'))->delete($filePath);
        }
        return false;
    }
}
