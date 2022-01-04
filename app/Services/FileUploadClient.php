<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class FileUploadClient implements FileUploadInterface
{
    /**
     * @inheritDoc
     */
    public function uploadFile(UploadedFile $fileToUpload, string $storagePath): string
    {
        $path = Storage::disk('local')->putFile($storagePath, $fileToUpload);

        return $path;
    }
}
