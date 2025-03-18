<?php

namespace App\Services\Image;

use App\Helpers\FileUtils;
use App\Helpers\S3Utils;

class ImageService
{
    static function uploadImage($file, string $folder, $objectId): ?string
    {
        $fileName = now()->format('Y-m-d') . "/" . $objectId . "/" . uniqid();
        $url = S3Utils::upload($file, $fileName, $folder);
        unlink($file->getPathname());
        return $url;
    }
}
