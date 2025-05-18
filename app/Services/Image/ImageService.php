<?php

namespace App\Services\Image;

use App\Helpers\FileUtils;
use App\Helpers\S3Utils;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    /**
     * Hàm xử lý nhiều ảnh (cho feedback)
     */
    static function uploadImages(array $images, string $folder, $objectId): array
    {
        return array_map(fn($image) => self::uploadImage($image, $folder, $objectId), array_filter($images));
    }

    /**
     * Hàm xử lý 1 ảnh duy nhất
     */
    static function uploadImage(string|UploadedFile $image , string $folder, $objectId): ?string
    {
        if (!$image) return null;

        $fileName = now()->format('Y-m-d') . "/" . $objectId . "/" . uniqid();

        if(is_string($image)){
            $file = FileUtils::convertBase64ToFile($image, 'image');
            $path = $file->storeAs($folder, $fileName, "supabase");

            $url = Storage::disk('s3')->url($path);
            unlink($file->getPathname());
        }else {
            $file = FileUtils::convertImageToWebp($image);
            $url = S3Utils::upload($file, $fileName, $folder);
        }
        return $url;
    }
}
