<?php

namespace App\Filament\Traits;
use App\Enums\InvoiceStatus;
use App\Models\Product;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

trait InvoiceLikeFormFields
{

    /**
     * Create a placeholder for the invoice ID.
     *
     * @return Placeholder
     */
    public static function invoiceIdPlaceholder(): Placeholder
    {
        return Placeholder::make('invoice_id')
            ->label('رقم الفاتورة')
            ->content(fn(?Model $record): ?string => $record?->id);
    }

    /**
     * Create a placeholder for the invoice date.
     *
     * @return Placeholder
     */
    public static function invoiceDatePlaceholder(): Placeholder
    {
        return Placeholder::make('invoice_date')
            ->label('تاريخ الفاتورة')
            ->content(fn(?Model $record): ?string => $record?->created_at?->format('Y-m-d h:i:s A'));
    }

    /**
     * Create a placeholder for the last update date.
     *
     * @return Placeholder
     */
    public static function updatedAtPlaceholder(): Placeholder
    {
        return Placeholder::make('updated_at')
            ->label('تاريخ اخر تحديث')
            ->content(fn(?Model $record): ?string => $record?->updated_at?->format('Y-m-d h:i:s A'));
    }

    /**
     * Create a placeholder for the officer responsible for the invoice.
     *
     * @return Placeholder
     */
    public static function officerPlaceholder(): Placeholder
    {
        return Placeholder::make('officer')
            ->label('المسؤول')
            ->content(auth()->user()->name);
    }

    /**
     * Create a placeholder for the total amount of the invoice.
     *
     * @param bool $showTotal
     * @return Placeholder
     */
    public static function totalPlaceholder(bool $showTotal = true): Placeholder
    {
        return Placeholder::make('total')
            ->label('المجموع')
            ->content(new HtmlString('<span x-text="computeInvoiceTotal"></span>'))
            ->visible($showTotal);
    }

    /**
     * Create a select component for the invoice status.
     *
     * @return Select
     */
    public static function statusSelect(): Select
    {
        return Select::make('status')
            ->label('الحالة')
            ->options(InvoiceStatus::toSelectArray())
            ->default('draft')
            ->required();
    }

    /**
     * Create the header section of the invoice form.
     *
     * @param bool $showTotal
     * @return array
     */
    public static function invoiceHeader($showTotal = true): array
    {
        return [
            Grid::make(4)
                ->schema([
                    self::invoiceIdPlaceholder(),
                    self::invoiceDatePlaceholder(),
                    self::updatedAtPlaceholder(),
                    self::officerPlaceholder(),
                    self::totalPlaceholder($showTotal),
                    self::statusSelect(),
                ]),
        ];
    }



}
