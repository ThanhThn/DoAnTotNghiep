<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class BaseRequest extends FormRequest
{
    protected function failedValidation(Validator $validator)
    {
     $error = (new ValidationException($validator))->errors();
     $mess = [];

     foreach ($error as $field => $messages) {
         foreach ($messages as $message) {
             $mess[] = [
                 'message' => $message,
                 'field' => $field,
             ];
         }
     }

     throw new HttpResponseException(response()->json([
         'status'  => JsonResponse::HTTP_BAD_REQUEST,
         'errors' => $mess,
     ], JsonResponse::HTTP_OK));
    }
}
