<?php

namespace App\Enums;

enum TranscriptionStatus: int
{
    case Pending = 0;
    case Processing = 1;
    case Done = 2;
    case Error = 3;

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Processing => 'Processing',
            self::Done => 'Done',
            self::Error => 'Error',
        };
    }

    public static function tryFromName(string $name): ?self
    {
        return match (strtolower($name)) {
            'pending' => self::Pending,
            'processing' => self::Processing,
            'done' => self::Done,
            'error' => self::Error,
            default => null,
        };
    }
}
