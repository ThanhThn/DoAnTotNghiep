<?php

namespace App\Jobs;

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
                    $data = substr($image, strpos($image, ',') + 1);
                    $data = base64_decode($data);
                    $fileName = uniqid();
                    // Lưu tạm
                    $tempFilePath = storage_path('app/temp_' . $fileName);
                    file_put_contents($tempFilePath, $data);

                    $file = new UploadedFile($tempFilePath, $fileName, 'image/webp', null, true);
                    $url = S3Utils::upload($file, '/'. now()->format('Y-m-d') ."/". $this->_objectId . "/" .$fileName, 'feedback');
                    unlink($tempFilePath);
                    $images[] = $url;
                }

                $body = $feedback->body;
                $body['images'] = $images;
                Log::info("Data", $body);
                $feedback->body = $body;
                $feedback->save();
//                $feedback->update(['body' => $body]);
            }; break;
        }
    }
}
