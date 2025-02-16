<?php

namespace App\Filament\Traits;

use App\Enums\InvoiceStatus;
use Filament\Tables;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;

trait InvoiceLikeFilters
{
    public static function filters()
    {
        return [
            self::statusFilter(),
            self::createdAtFilter(),
            self::updatedAtFilter(),
        ];
    }

    public static function statusFilter()
    {
        return Tables\Filters\SelectFilter::make('status')
            ->label('الحالة')
            ->options(
                InvoiceStatus::toSelectArray()
            );
    }

    public static function createdAtFilter()
    {
        return Tables\Filters\Filter::make('created_at')
            ->label('تاريخ الإنشاء')
            ->form([
                Forms\Components\DatePicker::make('created_from')
                    ->label('تاريخ الإنشاء - من تاريخ'),
                Forms\Components\DatePicker::make('created_until')
                    ->label('تاريخ الإنشاء - إلى تاريخ'),
            ])
            ->query(function (Builder $query, array $data) {
                return $query
                    ->when($data['created_from'], fn($query, $date) => $query->whereDate('created_at', '>=', $date))
                    ->when($data['created_until'], fn($query, $date) => $query->whereDate('created_at', '<=', $date));
            });
    }

    public static function updatedAtFilter()
    {
        return Tables\Filters\Filter::make('updated_at')
            ->label('تاريخ التحديث')
            ->form([
                Forms\Components\DatePicker::make('updated_from')
                    ->label('تاريخ التحديث - من تاريخ'),
                Forms\Components\DatePicker::make('updated_until')
                    ->label('تاريخ التحديث - إلى تاريخ'),
            ])
            ->query(function (Builder $query, array $data) {
                return $query
                    ->when($data['updated_from'], fn($query, $date) => $query->whereDate('updated_at', '>=', $date))
                    ->when($data['updated_until'], fn($query, $date) => $query->whereDate('updated_at', '<=', $date));
            });
    }
}
