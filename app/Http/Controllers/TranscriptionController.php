<?php

namespace App\Http\Controllers;

use App\Models\Transcription;
use Illuminate\Http\JsonResponse;

class TranscriptionController extends Controller
{
    public function status(Transcription $id): JsonResponse
    {
        return response()->json([
            'status' => $id->status,
            'text' => $id->transcription_text,
        ]);
    }
}
