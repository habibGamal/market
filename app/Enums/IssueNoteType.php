<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum IssueNoteType: string implements HasColor, HasIcon, HasLabel
{
    case ORDERS = 'orders';
    case RETURN_PURCHASES = 'return_purchases';
    case WASTE = 'waste';

    public function getColor(): ?string
    {
        return match($this) {
            self::ORDERS => 'blue',
            self::RETURN_PURCHASES => 'red',
            self::WASTE => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match($this) {
            self::ORDERS => 'heroicon-o-document-text',
            self::RETURN_PURCHASES => 'heroicon-o-arrow-uturn-left',
            self::WASTE => 'heroicon-o-trash',
        };
    }

    public function getLabel(): ?string
    {
        return match($this) {
            self::ORDERS => 'اذن صرف للطلبات',
            self::RETURN_PURCHASES => 'اذن صرف مرتجع مشتريات',
            self::WASTE => 'اذن صرف للهالك',
        };
    }

    public static function toSelectArray(): array
    {
        return [
            self::ORDERS->value => self::ORDERS->getLabel(),
            self::RETURN_PURCHASES->value => self::RETURN_PURCHASES->getLabel(),
            self::WASTE->value => self::WASTE->getLabel(),
        ];
    }
}
