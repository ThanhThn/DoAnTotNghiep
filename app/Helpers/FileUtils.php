<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Laravel\Facades\Image;

class FileUtils
{
    public static function convertBase64ToFile(string $data, string $type): UploadedFile
    {

        $types = [
            'image' => ['ext' => 'webp', 'mime' => 'image/webp'],
            'video' => ['ext' => 'mp4', 'mime' => 'video/mp4']
        ];

        $fileName = uniqid() . '.' . $types[$type]['ext'];
        $mimeType = $types[$type]['mime'];
        $tempFilePath = storage_path("app/temp_$fileName");

        $data = base64_decode(substr($data, strpos($data, ',') + 1));

        file_put_contents($tempFilePath, $data);

        if ($type === 'image') {
            $image = self::convertImageToWebp($tempFilePath);
            file_put_contents($tempFilePath, (string) $image);
        }


        // Tạo UploadedFile
        return new UploadedFile($tempFilePath, $fileName, $mimeType, null, true);
    }

    public static function convertImageToWebp($file){
        return Image::read($file)->toWebp(90);
    }
}
