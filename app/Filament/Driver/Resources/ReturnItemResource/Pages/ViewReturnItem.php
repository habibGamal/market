<?php

namespace App\Filament\Driver\Resources\ReturnItemResource\Pages;

use App\Filament\Driver\Resources\ReturnItemResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;

class ViewReturnItem extends ViewRecord
{
    protected static string $resource = ReturnItemResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('معلومات المرتجع')
                    ->schema([
                        TextEntry::make('order.id')
                            ->label('رقم الطلب'),
                        TextEntry::make('product.name')
                            ->label('المنتج'),
                        TextEntry::make('packets_quantity')
                            ->label('عدد العبوات'),
                        TextEntry::make('piece_quantity')
                            ->label('عدد القطع'),
                        TextEntry::make('total')
                            ->label('الإجمالي')
                            ->money('EGP'),
                        TextEntry::make('status')
                            ->label('الحالة')
                            ->badge(),
                        TextEntry::make('return_reason')
                            ->label('سبب الإرجاع'),
                        TextEntry::make('notes')
                            ->label('ملاحظات'),
                        TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label('آخر تحديث')
                            ->dateTime(),
                    ])->columns(3),

                Section::make('معلومات العميل')
                    ->schema([
                        TextEntry::make('order.customer.name')
                            ->label('اسم العميل'),
                        TextEntry::make('order.customer.phone')
                            ->label('رقم الهاتف'),
                        TextEntry::make('order.customer.area.name')
                            ->label('المنطقة'),
                        TextEntry::make('order.customer.address')
                            ->label('العنوان')
                            ->columnSpanFull(),
                    ])->columns(3)
            ]);
    }
}
