<?php

namespace App\Jobs;

use App\Helpers\FileUtils;
use App\Helpers\S3Utils;
use App\Models\Equipment;
use App\Models\Feedback;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class UploadImageToStorage implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */

    private string $_objectId;
    private $_type;
    private $_images;
    public function __construct($objectId, $type, $images)
    {
        $this->_objectId = $objectId;
        $this->_type = $type;
        $this->_images = $images;
    }

    /**
     * Execute the job.
     */
    public function handle(): void{

    switch ($this->_type) {
        case config('constant.type.feedback'):
            $feedback = Feedback::find($this->_objectId);
            if (!$feedback) {
                return;
            }

            $images = $this->uploadImages($this->_images, 'feedback');
            $body = $feedback->body;
            $body['images'] = $images;
            $feedback->body = $body;
            $feedback->save();
            break;

        case config('constant.type.equipment'):
            $equipment = Equipment::find($this->_objectId);
            if (!$equipment || empty($this->_images)) {
                return;
            }

            $url = $this->uploadImage($this->_images, 'equipment');
            $equipment->thumbnail = $url;
            $equipment->save();
            break;
    }
}

    /**
     * Hàm xử lý nhiều ảnh (cho feedback)
     */
    private function uploadImages(array $images, string $folder): array
    {
        return array_map(fn($image) => $this->uploadImage($image, $folder), array_filter($images));
    }

    /**
     * Hàm xử lý 1 ảnh duy nhất
     */
    private function uploadImage(string $image, string $folder): ?string
    {
        if (!$image) return null;

        $fileName = now()->format('Y-m-d') . "/" . $this->_objectId . "/" . uniqid();
        $file = FileUtils::convertBase64ToFile($image, 'image');

        $url = S3Utils::upload($file, $fileName, $folder);
        unlink($file->getPathname());

        return $url;
    }
}
