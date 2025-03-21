<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum NotificationStatus: string implements HasColor, HasIcon, HasLabel
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    public function getColor(): ?string
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::PROCESSING => 'info',
            self::COMPLETED => 'success',
            self::FAILED => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PENDING => 'heroicon-o-clock',
            self::PROCESSING => 'heroicon-o-arrow-path',
            self::COMPLETED => 'heroicon-o-check-circle',
            self::FAILED => 'heroicon-o-x-circle',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => 'قيد الانتظار',
            self::PROCESSING => 'جاري الإرسال',
            self::COMPLETED => 'تم الإرسال',
            self::FAILED => 'فشل الإرسال',
        };
    }

    public static function toSelectArray(): array
    {
        return [
            self::PENDING->value => self::PENDING->getLabel(),
            self::PROCESSING->value => self::PROCESSING->getLabel(),
            self::COMPLETED->value => self::COMPLETED->getLabel(),
            self::FAILED->value => self::FAILED->getLabel(),
        ];
    }
}
