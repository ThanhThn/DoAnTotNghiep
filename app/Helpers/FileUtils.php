<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;

class FileUtils
{
    public static function convertBase64ToFile(string $data, string $type): UploadedFile
    {
        $data = base64_decode(substr($data, strpos($data, ',') + 1));
        $types = [
            'image' => ['ext' => 'webp', 'mime' => 'image/webp'],
            'video' => ['ext' => 'mp4', 'mime' => 'video/mp4']
        ];

        $fileName = uniqid() . '.' . $types[$type]['ext'];
        $mimeType = $types[$type]['mime'];

        $tempFilePath = storage_path("app/temp_$fileName");
        file_put_contents($tempFilePath, $data);

        // Tạo UploadedFile
        return new UploadedFile($tempFilePath, $fileName, $mimeType, null, true);
    }
}
