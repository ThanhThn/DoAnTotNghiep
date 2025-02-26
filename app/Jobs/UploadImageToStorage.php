<?php

namespace App\Jobs;

use App\Helpers\FileUtils;
use App\Helpers\S3Utils;
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
    public function handle(): void
    {
        switch ($this->_type) {
            case config('constant.type.feedback'): {
                $feedback = Feedback::find($this->_objectId);
                $images = [];
                foreach ($this->_images as $image) {
                    $fileName = now()->format('Y-m-d') . "/" . $this->_objectId . "/" . uniqid() . ".webp";
                    Log::info($image);
                    $file = FileUtils::convertBase64ToFile($image, 'image');
                    $url = S3Utils::upload($file, $fileName, 'feedback');
                    unlink($file->getPathname());
                    $images[] = $url;
                }

                $body = $feedback->body;
                $body['images'] = $images;
                $feedback->body = $body;
                $feedback->save();
            } break;
        }
    }
}
