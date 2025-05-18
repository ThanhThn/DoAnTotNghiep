<?php

namespace App\Services\Image;

use App\Helpers\FileUtils;
use App\Helpers\S3Utils;
use Illuminate\Http\UploadedFile;

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
        }else {
            $file = FileUtils::convertImageToWebp($image);
        }

        $url = S3Utils::upload($file, $fileName, $folder);
        // Nếu $file là file tạm do convertBase64ToFile tạo ra thì mới unlink
        if (is_string($image) && $file && file_exists($file->getPathname())) {
            unlink($file->getPathname());
        }

        return $url;
    }
}
