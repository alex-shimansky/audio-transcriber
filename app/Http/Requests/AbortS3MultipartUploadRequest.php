<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AbortS3MultipartUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'uploadId' => 'required|string',
            'key' => 'required|string',
        ];
    }
}
