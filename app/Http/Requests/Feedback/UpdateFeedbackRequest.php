<?php

namespace App\Http\Requests\Feedback;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateFeedbackRequest extends BaseRequest
{
    public function rules(){
        return [
          'lodging_id' => 'required|uuid|exists:lodgings,id',
          'feedback_id' => ['required','uuid','exists:feedbacks,id',                 Rule::exists('feedbacks', 'id')->where(function ($query) {
              return $query->where('lodging_id', $this->input('lodging_id'));
          }),],
          'status' => 'required|integer|in:1,2,3,4,5,6'
        ];
    }
}
