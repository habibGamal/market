<?php

namespace App\Filament\Resources;

use App\Enums\InvoiceStatus;
use App\Enums\IssueNoteType;
use App\Enums\PaymentStatus;
use App\Filament\Interfaces\InvoiceResource;
use App\Filament\Resources\IssueNoteResource\Pages;
use App\Filament\Resources\IssueNoteResource\RelationManagers;
use App\Models\IssueNote;
use Awcodes\TableRepeater\Components\TableRepeater;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Awcodes\TableRepeater\Header;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use App\Filament\Exports\IssueNoteExporter;

class IssueNoteResource extends InvoiceResource implements HasShieldPermissions
{
    protected static ?string $model = IssueNote::class;

    protected static ?string $navigationGroup = 'إدارة المخزن';

    protected static ?string $navigationIcon = 'heroicon-o-document';

    protected static ?string $modelLabel = 'اذن صرف';

    protected static ?string $pluralModelLabel = 'اذونات الصرف';

    public static function csvTitles(): array
    {
        return [
            'product_id' => 'الرقم المرجعي للمنتج',
            'product_name' => 'المنتج',
            'quantity' => 'الكمية',
            'price' => 'سعر العبوة',
            'total' => 'الإجمالي',
        ];
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                ...self::invoiceHeader(showTotal: false),
                TableRepeater::make('items')
                    ->label('عناصر الفاتورة')
                    ->relationship('items', fn($query) => $query->with('product:id,name'))
                    ->extraActions([
                        // self::exportCSVAction(),
                        // self::importCSVAction(),
                    ])
                    ->headers([
                        Header::make('product_name')->label('المنتج')->width('150px'),
                        Header::make('packets_quantity')->label('عدد العبوات')->width('150px'),
                        Header::make('piece_quantity')->label('عدد القطع')->width('150px'),
                        Header::make('release_date')->label('تاريخ الانتاج')->width('150px'),
                    ])
                    ->schema([
                        Forms\Components\Hidden::make('product_id'),
                        Forms\Components\TextInput::make('product_name')
                            ->label('المنتج')
                            ->formatStateUsing(fn($state, $record) => $record ? $record->product_name : $state),
                        Forms\Components\TextInput::make('packets_quantity')
                            ->label('عدد العبوات')
                            ->numeric(),
                        Forms\Components\TextInput::make('piece_quantity')
                            ->label('عدد القطع')
                            ->numeric(),
                        Forms\Components\TextInput::make('release_date')
                            ->label('تاريخ الانتاج'),
                    ])
                    ->disabled()
                    ->mutateRelationshipDataBeforeCreateUsing(fn(array $data) => static::mutateItemsBeforeSaving($data))
                    ->mutateRelationshipDataBeforeSaveUsing(fn(array $data) => static::mutateItemsBeforeSaving($data))
                    ->columnSpan('full')
                    ->addable(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('رقم الإذن')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('المجموع')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->label('الحالة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('note_type')
                    ->badge()
                    ->label('نوع الإذن')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->label('حالة القبض')
                    ->formatStateUsing(fn($record) => $record->note_type === IssueNoteType::RETURN_PURCHASES ? $record->payment_status : null)
                    ->sortable(),
                Tables\Columns\TextColumn::make('officer.name')
                    ->label('المسؤول')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('note_type')
                    ->label('نوع الإذن')
                    ->options(IssueNoteType::toSelectArray())
                    ->searchable()
                    ->multiple(),
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(InvoiceStatus::toSelectArray())
                    ->searchable()
                    ->multiple(),
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->exporter(IssueNoteExporter::class),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            \Filament\Infolists\Components\Actions::make([
                printAction(\Filament\Infolists\Components\Actions\Action::make('print')),
            ])->columnSpanFull()
                ->alignEnd(),
            TextEntry::make('id')
                ->label('رقم الإذن'),
            TextEntry::make('total')
                ->label('المجموع')
                ->visible(fn() => auth()->user()->can('show_costs_issue::note')),
            TextEntry::make('status')
                ->badge()
                ->label('الحالة'),
            TextEntry::make('note_type')
                ->badge()
                ->label('نوع الإذن')
                ->suffixAction(
                    \Filament\Infolists\Components\Actions\Action::make('viewDocument')
                        ->label('عرض المستند')
                        ->url(fn(Model $record) => match ($record->note_type) {
                            IssueNoteType::WASTE => WasteResource::getUrl('view', ['record' => $record->waste->id]),
                            IssueNoteType::RETURN_PURCHASES => ReturnPurchaseInvoiceResource::getUrl('view', ['record' => $record->returnPurchaseInvoice->id]),
                            default => '#'
                        })
                        ->icon('heroicon-m-arrow-top-right-on-square')
                        ->openUrlInNewTab()
                ),
            TextEntry::make('payment_status')
                ->badge()
                ->label('حالة القبض')
                ->visible(fn($record) => $record->note_type === IssueNoteType::RETURN_PURCHASES),
            TextEntry::make('total_paid')
                ->label('إجمالي المقبوض')
                ->money('EGP')
                ->visible(fn($record) => $record->note_type === IssueNoteType::RETURN_PURCHASES && $record->total_paid > 0),
            TextEntry::make('remaining_amount')
                ->label('المبلغ المتبقي')
                ->money('EGP')
                ->visible(fn($record) => $record->note_type === IssueNoteType::RETURN_PURCHASES && $record->remaining_amount > 0),
            TextEntry::make('officer.name')
                ->label('المسؤول'),
            TextEntry::make('created_at')
                ->label('تاريخ الإنشاء')
                ->dateTime(),
            TextEntry::make('updated_at')
                ->label('تاريخ التحديث')
                ->dateTime(),
            TextEntry::make('orders')
                ->label('العملاء')
                ->formatStateUsing(function ($record) {
                    return $record->orders->load('customer')->map(function ($order) {
                        $url = OrderResource::getUrl('view', ['record' => $order->id]);
                        return '<a href="' . $url . '" class="text-primary-600 hover:underline" target="_blank">' . e($order->customer->name) . '</a>';
                    })->join(', ');
                })
                ->html()
                ->columnSpanFull()
                ->visible(fn($record) => $record->note_type === IssueNoteType::ORDERS),

            TextEntry::make('notes')
                ->label('ملاحظات')
                ->columnSpanFull(),
        ])
            ->columns(3);
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'show_costs',
        ];
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\AccountantReceiptNotesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIssueNotes::route('/'),
            'create' => Pages\CreateIssueNote::route('/create'),
            'view' => Pages\ViewIssueNote::route('/{record}'),
            'edit' => Pages\EditIssueNote::route('/{record}/edit'),
        ];
    }
}
