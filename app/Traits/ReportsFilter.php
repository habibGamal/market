<?php

namespace App\Traits;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;

trait ReportsFilter
{
    public const PERIOD_TODAY = 'today';
    public const PERIOD_THIS_WEEK = 'this_week';
    public const PERIOD_THIS_MONTH = 'this_month';
    public const PERIOD_LAST_3_MONTHS = 'last_3_months';
    public const PERIOD_LAST_6_MONTHS = 'last_6_months';
    public const PERIOD_LAST_YEAR = 'last_year';
    public const PERIOD_CUSTOM = 'custom';

    public static function filtersForm()
    {
        return [
            Select::make('period')
                ->label('الفترة')
                ->options([
                    self::PERIOD_TODAY => 'اليوم',
                    self::PERIOD_THIS_WEEK => 'هذا الأسبوع',
                    self::PERIOD_THIS_MONTH => 'هذا الشهر',
                    self::PERIOD_LAST_3_MONTHS => 'آخر 3 أشهر',
                    self::PERIOD_LAST_6_MONTHS => 'آخر 6 أشهر',
                    self::PERIOD_LAST_YEAR => 'السنة الماضية',
                    self::PERIOD_CUSTOM => 'فترة مخصصة',
                ])
                ->default(self::PERIOD_THIS_MONTH)
                ->reactive()
                ->afterStateUpdated(
                    function ($state, $set) {
                        static::updateDateRange($state, $set);
                    }
                )
            ,

            DatePicker::make('start_date')
                ->label('من تاريخ')
                ->displayFormat('Y-m-d')
                ->default(now()->startOfMonth()->format('Y-m-d'))
                ->visible(fn($get) => $get('period') === self::PERIOD_CUSTOM),

            DateTimePicker::make('end_date')
                ->label('إلى تاريخ')
                ->displayFormat('Y-m-d H:i:s')
                ->default(now()->format('Y-m-d H:i:s'))
                ->visible(fn($get) => $get('period') === self::PERIOD_CUSTOM),
        ];
    }

    public static function updateDateRange($state, $set): void
    {
        switch ($state) {
            case self::PERIOD_TODAY:
                $set('start_date', now()->startOfDay()->format('Y-m-d'));
                $set('end_date', now()->format('Y-m-d H:i:s'));
                break;

            case self::PERIOD_THIS_WEEK:
                $set('start_date', now()->startOfWeek()->format('Y-m-d'));
                $set('end_date', now()->format('Y-m-d H:i:s'));
                break;

            case self::PERIOD_THIS_MONTH:
                $set('start_date', now()->startOfMonth()->format('Y-m-d'));
                $set('end_date', now()->format('Y-m-d H:i:s'));
                break;

            case self::PERIOD_LAST_3_MONTHS:
                $set('start_date', now()->subMonths(3)->format('Y-m-d'));
                $set('end_date', now()->format('Y-m-d H:i:s'));
                break;

            case self::PERIOD_LAST_6_MONTHS:
                $set('start_date', now()->subMonths(6)->format('Y-m-d'));
                $set('end_date', now()->format('Y-m-d H:i:s'));
                break;

            case self::PERIOD_LAST_YEAR:
                $set('start_date', now()->subYear()->format('Y-m-d'));
                $set('end_date', now()->format('Y-m-d H:i:s'));
                break;
        }
    }

    public static function getRange($state)
    {
        return match ($state) {
            self::PERIOD_TODAY => [
                'start_date' => now()->startOfDay()->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d H:i:s'),
            ],
            self::PERIOD_THIS_WEEK => [
                'start_date' => now()->startOfWeek()->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d H:i:s'),
            ],
            self::PERIOD_THIS_MONTH => [
                'start_date' => now()->startOfMonth()->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d H:i:s'),
            ],
            self::PERIOD_LAST_3_MONTHS => [
                'start_date' => now()->subMonths(3)->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d H:i:s'),
            ],
            self::PERIOD_LAST_6_MONTHS => [
                'start_date' => now()->subMonths(6)->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d H:i:s'),
            ],
            self::PERIOD_LAST_YEAR => [
                'start_date' => now()->subYear()->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d H:i:s'),
            ],
            self::PERIOD_CUSTOM => [
                'start_date' => request('start_date'),
                'end_date' => request('end_date'),
            ],
        };
    }
}
