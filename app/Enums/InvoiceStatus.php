<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum InvoiceStatus: string implements HasColor , HasIcon , HasLabel
{
    case DRAFT = 'draft';
    case CLOSED = 'closed';

    public function getColor(): ?string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::CLOSED => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::DRAFT => 'heroicon-o-document-duplicate',
            self::CLOSED => 'heroicon-o-check-circle',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DRAFT => 'مسودة',
            self::CLOSED => 'مغلقة',
        };
    }

    public static function toSelectArray(): array
    {
        return [
            self::DRAFT->value => self::DRAFT->getLabel(),
            self::CLOSED->value => self::CLOSED->getLabel(),
        ];
    }
}
