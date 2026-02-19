<?php

namespace App\Helpers;

use App\Models\VehicleDocument;
use Illuminate\Http\UploadedFile;

class FileHelper
{
    /**
     * Upload a file and store its record in the vehicle_documents table.
     *
     * @return VehicleDocument
     */
    public static function uploadVehicleDocument(
        UploadedFile $file,
        int $vehicleId,
        ?string $documentType = null,
        ?string $documentNumber = null,
        ?string $expiryDate = null,
        string $disk = 'public'
    ) {
        $path = $file->store('vehicle_documents', $disk);
        $document = VehicleDocument::create([
            'vehicle_id' => $vehicleId,
            'document_type' => $documentType,
            'document_path' => $path,
            'document_number' => $documentNumber,
            'expiry_date' => $expiryDate,
            'status' => 'active',
        ]);

        return $document;
    }
}
