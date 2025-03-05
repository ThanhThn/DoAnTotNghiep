<?php

namespace App\Http\Requests\Feedback;

use App\Http\Requests\BaseRequest;

class DetailFeedbackRequest extends BaseRequest
{
    public function rules(){
        return [
            'feedbackId' => 'required|uuid|exists:feedbacks,id',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'feedbackId' => $this->route('feedbackId'),
        ]);
    }
}
