<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\TranscriptionStatus;

class Transcription extends Model
{
    protected $fillable = [
        'email',
        's3_key',
        'status',
        'transcription_text',
        'job_name',
        'error_message',
    ];

    public function getStatusAttribute($value): TranscriptionStatus
    {
        return TranscriptionStatus::from((int)$value);
    }

    public function setStatusAttribute(TranscriptionStatus|int|string $value): void
    {
        if ($value instanceof TranscriptionStatus) {
            $this->attributes['status'] = $value->value;
        } elseif (is_string($value)) {
            // Пробуем преобразовать строку с именем статуса в числовое значение enum
            $status = TranscriptionStatus::tryFromName($value);
            if ($status !== null) {
                $this->attributes['status'] = $status->value;
            } else {
                // Если не удалось распознать, то можно по умолчанию Pending (0)
                $this->attributes['status'] = TranscriptionStatus::Pending->value;
            }
        } else {
            // Для int
            $this->attributes['status'] = $value;
        }
    }
}
