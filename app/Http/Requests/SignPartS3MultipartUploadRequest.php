<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SignPartS3MultipartUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => 'required|string',
            'uploadId' => 'required|string',
            'partNumber' => 'required|integer',
        ];
    }
}
