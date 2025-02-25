<?php

namespace App\Helpers;

use Aws\S3\MultipartUploader;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class S3Utils
{
    static function upload($file, $fileName , $store)
    {
        $path = $file->storeAs($store, $fileName, 'supabase');
        // Lấy URL của file vừa tải lên
        return Storage::disk('supabase')->url($path);
    }

    static function delete($filePath)
    {
        $key = parse_url($filePath, PHP_URL_PATH);
        $key = ltrim($key, '/');
        return Storage::disk('supabase')->delete($key);
    }

    public function uploadLargeFile(string $store, $file, $fileName)
    {
        $path = $store .  '/' . $fileName;
        Storage::disk('supabase')->put($path, $file , 'supabase');
        return $this->getObjectUrlFromS3($path);
    }

    /**
     * getUrlFromS3
     *
     * @param string|array $pathFile
     * @return void
     */
    public function getObjectUrlFromS3(string|array $pathFile): string|array
    {
        if (empty($pathFile)) {
            return '';
        }
        if (is_array($pathFile)) {
            $arrPathExist = [];
            foreach ($pathFile as $item) {
                if (Storage::disk('supabase')->exists($item)) {
                    $arrPathExist[] = Storage::disk('supabase')->url($item);
                }
            }

            if(!empty($arrPathExist)) {
                return $arrPathExist;
            }
        }
        if (is_string($pathFile) && Storage::disk('supabase')->exists($pathFile)) {
            return Storage::disk('supabase')->url($pathFile);
        }
    }
}
