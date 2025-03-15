<?php

namespace App\Filament\Resources\Reports\DriversReportResource\RelationManagers;

use App\Enums\DriverStatus;
use App\Filament\Resources\OrderResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';

    protected static ?string $title = 'الطلبيات المخصصة للسائق';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('رقم المهمة')
                    ->sortable(),
                TextColumn::make('order.id')
                    ->label('رقم الطلب')
                    ->sortable(),
                TextColumn::make('order.customer.name')
                    ->label('اسم العميل')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order.total')
                    ->label('قيمة الطلب')
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),
                TextColumn::make('assismentOfficer.name')
                    ->label('تم التعيين بواسطة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('تاريخ التعيين')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('عرض الطلب')
                    ->icon('heroicon-m-eye')
                    ->url(fn ($record) => OrderResource::getUrl('view', ['record' => $record->order_id]))
            ])
            ->bulkActions([
                //
            ]);
    }
}
