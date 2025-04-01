<?php

namespace App\Filament\Resources\Reports\ProductsReportResource\RelationManagers;

use App\Enums\InvoiceStatus;
use App\Filament\Exports\ReturnPurchaseItemsExporter;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Table;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

class ReturnPurchaseItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'returnPurchaseItems';
    protected static ?string $title = 'مرتجعات المشتريات';
    protected static ?string $modelLabel = 'مرتجع مشتريات';
    protected static ?string $pluralModelLabel = 'مرتجعات المشتريات';


    #[Url]
    public $start;
    #[Url]
    public $end;

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                return $query->whereHas('returnPurchaseInvoice', function ($query) {
                    $query->whereHas('issueNote', function ($query) {
                        $query->where('status', InvoiceStatus::CLOSED);
                    });

                    if ($this->start && $this->end) {
                        $query->whereBetween('created_at', [$this->start, $this->end]);
                    }
                });
            })
            ->columns([
                Tables\Columns\TextColumn::make('returnPurchaseInvoice.id')
                    ->label('رقم الفاتورة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('packets_quantity')
                    ->label('عدد العبوات')
                    ->sortable(),
                Tables\Columns\TextColumn::make('packet_cost')
                    ->label('تكلفة العبوة')
                    ->money('EGP')
                    ->visible(fn() => auth()->user()->can('show_costs_return::purchase')),
                Tables\Columns\TextColumn::make('release_date')
                    ->label('تاريخ الإنتاج')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('الإجمالي')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('returnPurchaseInvoice.supplier.name')
                    ->label('المورد')
                    ->sortable(),
                Tables\Columns\TextColumn::make('returnPurchaseInvoice.status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('returnPurchaseInvoice.created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('تصدير')
                    ->exporter(ReturnPurchaseItemsExporter::class),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn($record) => \App\Filament\Resources\ReturnPurchaseInvoiceResource::getUrl('view', [
                        'record' => $record->returnPurchaseInvoice,
                    ]))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateIcon('heroicon-o-arrow-path')
            ->emptyStateHeading('لا توجد مرتجعات مشتريات')
            ->emptyStateDescription('هذا المنتج ليس له مرتجعات مشتريات حتى الآن');
    }
}
