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
        $storage = Storage::disk('supabase');
        $path = $store .  '/' . $fileName;
        $storage->put($path, $file , 'supabase');
        return $storage->url($path);
    }

    static function delete(array $urls)
    {
        $storage = Storage::disk('supabase');
        $pattern = '/\/object\/public\/[^\/]+\/(.*)/';
        $paths = array_map(function ($url) use ($pattern) {
            $parsedUrl = parse_url($url);
            $path = $parsedUrl['path'] ?? '';

            if (preg_match($pattern, $path, $matches)) {
                return $matches[1];
            }

            return null;
        }, $urls);

        $paths = array_filter($paths);

        return $storage->delete($paths);
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
