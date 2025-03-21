<?php

namespace App\Filament\Resources\NotificationManagerResource\Pages;

use App\Filament\Resources\NotificationManagerResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewNotificationManager extends ViewRecord
{
    protected static string $resource = NotificationManagerResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\TextEntry::make('title')
                            ->label('عنوان الإشعار'),

                        Infolists\Components\TextEntry::make('notification_type')
                            ->label('نوع الإشعار')
                            ->formatStateUsing(fn ($state) => $state ? $state->getLabel() : '-'),

                        Infolists\Components\TextEntry::make('body')
                            ->label('نص الإشعار')
                            ->markdown(),

                        Infolists\Components\TextEntry::make('data.action_url')
                            ->label('رابط الإجراء')
                            ->formatStateUsing(fn ($state) => $state ? "<a href='{$state}' target='_blank'>{$state}</a>" : '-')
                            ->html()
                            ->visible(fn ($record) => !empty($record->data['action_url'])),

                        Infolists\Components\TextEntry::make('filters')
                            ->label('عوامل التصفية')
                            ->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT))
                            ->prose(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('إحصائيات')
                    ->schema([
                        Infolists\Components\TextEntry::make('total_recipients')
                            ->label('إجمالي المستلمين')
                            ->numeric(),

                        Infolists\Components\TextEntry::make('successful_sent')
                            ->label('تم الإرسال بنجاح')
                            ->numeric(),

                        Infolists\Components\TextEntry::make('failed_sent')
                            ->label('فشل الإرسال')
                            ->numeric(),

                        Infolists\Components\TextEntry::make('read_count')
                            ->label('عدد القراءات')
                            ->numeric(),

                        Infolists\Components\TextEntry::make('click_count')
                            ->label('عدد النقرات')
                            ->numeric(),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('معلومات التوقيت')
                    ->schema([
                        Infolists\Components\TextEntry::make('schedule_at')
                            ->label('موعد الإرسال المجدول')
                            ->dateTime(),

                        Infolists\Components\TextEntry::make('sent_at')
                            ->label('تم الإرسال في')
                            ->dateTime(),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->dateTime(),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('سجل الأخطاء')
                    ->schema([
                        Infolists\Components\TextEntry::make('error_log')
                            ->label('سجل الأخطاء')
                            ->visible(fn ($record) => !empty($record->error_log))
                            ->badge()
                            ->color('danger'),
                    ])
                    ->collapsed()
                    ->collapsible(),
            ]);
    }
}
