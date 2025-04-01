<?php

namespace App\Filament\Resources;

use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use App\Filament\Resources\NotificationManagerResource\Pages;
use App\Models\NotificationManager;
use App\Models\Gov;
use App\Models\City;
use App\Models\Area;
use App\Models\BusinessType;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Tabs;
use Filament\Tables\Actions\Action;

class NotificationManagerResource extends Resource
{
    protected static ?string $model = NotificationManager::class;
    protected static ?string $navigationIcon = 'heroicon-o-bell';
    protected static ?string $navigationGroup = 'إدارة النظام';
    protected static ?string $navigationLabel = 'إدارة الإشعارات';
    protected static ?string $pluralModelLabel = 'الإشعارات';
    protected static ?string $modelLabel = 'إشعار';
    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('Notification')
                ->tabs([
                    Tabs\Tab::make('محتوى الإشعار')
                        ->schema([
                            Forms\Components\TextInput::make('title')
                                ->label('عنوان الإشعار')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\Select::make('notification_type')
                                ->label('نوع الإشعار')
                                ->options(NotificationType::class)
                                ->enum(NotificationType::class)
                                ->required()
                                ->default(NotificationType::GENERAL->value),

                            Forms\Components\Textarea::make('body')
                                ->label('نص الإشعار')
                                ->required()
                                ->maxLength(65535),

                            Forms\Components\TextInput::make('data.action_url')
                                ->label('رابط الإجراء')
                                ->url()
                                ->placeholder('https://example.com')
                                ->helperText('اتركه فارغاً إذا لم يكن هناك رابط للإجراء'),

                            Forms\Components\DateTimePicker::make('schedule_at')
                                ->label('موعد الإرسال')
                                ->nullable()
                                ->helperText('اتركه فارغاً للإرسال الفوري'),
                        ]),

                    Tabs\Tab::make('عوامل التصفية')
                        ->schema([
                            Forms\Components\Section::make('تصفية حسب المناطق')
                                ->schema([
                                    Forms\Components\Select::make('filters.governorates')
                                        ->label('المحافظات')
                                        ->options(fn() => Gov::pluck('name', 'id'))
                                        ->multiple()
                                        ->searchable()
                                        ->preload(),

                                    Forms\Components\Select::make('filters.cities')
                                        ->label('المدن')
                                        ->options(fn() => City::pluck('name', 'id'))
                                        ->multiple()
                                        ->searchable()
                                        ->preload(),

                                    Forms\Components\Select::make('filters.areas')
                                        ->label('المناطق')
                                        ->options(fn() => Area::pluck('name', 'id'))
                                        ->multiple()
                                        ->searchable()
                                        ->preload(),
                                ])->columns(3),

                            Forms\Components\Section::make('تصفية حسب نوع النشاط والحالة')
                                ->schema([
                                    Forms\Components\Select::make('filters.business_types')
                                        ->label('نوع النشاط')
                                        ->options(fn() => BusinessType::pluck('name', 'id'))
                                        ->multiple()
                                        ->searchable()
                                        ->preload(),

                                    Forms\Components\Toggle::make('filters.active_only')
                                        ->label('العملاء النشطين فقط')
                                        ->default(true),

                                    Forms\Components\Toggle::make('filters.has_orders')
                                        ->label('لديهم طلبات سابقة')
                                        ->default(false),
                                ])->columns(3),

                            Forms\Components\Section::make('تصفية حسب التقييم والنقاط')
                                ->schema([
                                    Forms\Components\TextInput::make('filters.min_points')
                                        ->label('الحد الأدنى للنقاط')
                                        ->numeric()
                                        ->minValue(0),

                                    Forms\Components\TextInput::make('filters.max_points')
                                        ->label('الحد الأقصى للنقاط')
                                        ->numeric()
                                        ->minValue(0),
                                ])->columns(2),
                        ]),
                ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('notification_type')
                    ->label('نوع الإشعار')
                    ->badge(),

                Tables\Columns\TextColumn::make('total_recipients')
                    ->label('عدد المستلمين')
                    ->sortable(),

                Tables\Columns\TextColumn::make('successful_sent')
                    ->label('تم الإرسال')
                    ->sortable(),

                Tables\Columns\TextColumn::make('failed_sent')
                    ->label('فشل الإرسال')
                    ->sortable(),

                Tables\Columns\TextColumn::make('read_count')
                    ->label('عدد القراءات')
                    ->sortable(),

                Tables\Columns\TextColumn::make('click_count')
                    ->label('عدد النقرات')
                    ->sortable(),

                Tables\Columns\IconColumn::make('data.action_url')
                    ->label('رابط الإجراء')
                    ->boolean()
                    ->trueIcon('heroicon-o-link')
                    ->falseIcon('heroicon-o-x-mark')
                    ->tooltip(fn($record) => $record->data['action_url'] ?? 'لا يوجد رابط'),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),

                Tables\Columns\TextColumn::make('schedule_at')
                    ->label('موعد الإرسال')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sent_at')
                    ->label('تم الإرسال في')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(NotificationStatus::class),

                Tables\Filters\SelectFilter::make('notification_type')
                    ->label('نوع الإشعار')
                    ->options(NotificationType::class),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('test')
                    ->label('اختبار الإشعار')
                    ->icon('heroicon-o-play')
                    ->form([
                        Forms\Components\TextInput::make('phone')
                            ->label('رقم هاتف العميل')
                            ->tel()
                            ->required()
                            ->placeholder('ادخل رقم هاتف العميل'),
                    ])
                    ->action(function (NotificationManager $record, array $data): void {
                        $customer = Customer::where('phone', $data['phone'])->firstOrFail();

                        dispatch(new \App\Jobs\ProcessCustomerNotificationJob($record, $customer));
                    })
                    ->successNotification(
                        notification: fn() => \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('تم إرسال الإشعار التجريبي')
                            ->body('تم إرسال الإشعار إلى العميل بنجاح')
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotificationManagers::route('/'),
            'create' => Pages\CreateNotificationManager::route('/create'),
            'edit' => Pages\EditNotificationManager::route('/{record}/edit'),
            'view' => Pages\ViewNotificationManager::route('/{record}'),
        ];
    }
}
