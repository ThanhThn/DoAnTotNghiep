<?php

namespace App\Jobs;

use App\Helpers\FileUtils;
use App\Helpers\S3Utils;
use App\Models\Equipment;
use App\Models\Feedback;
use App\Services\Image\ImageService;
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

            $images = ImageService::uploadImages($this->_images, 'feedback', $this->_objectId);
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

            $url = ImageService::uploadImage($this->_images, 'equipment', $this->_objectId);
            $equipment->thumbnail = $url;
            $equipment->save();
            break;
    }
}
}
