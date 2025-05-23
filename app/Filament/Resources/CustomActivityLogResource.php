<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomActivityLogResource\Pages\ListCustomActivityLogs;
use App\Filament\Resources\CustomActivityLogResource\Pages\ViewCustomActivityLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables\Columns\Column;
use Illuminate\Database\Eloquent\Model;
use Rmsramos\Activitylog\Actions\Concerns\ActionContent;
use \Rmsramos\Activitylog\Resources\ActivitylogResource;
use \Rmsramos\Activitylog\Resources\ActivitylogResource\Pages\ViewActivitylog;
class CustomActivityLogResource extends ActivitylogResource
{
    use ActionContent;
    protected static ?string $slug = 'activitylogs';

    protected static ?string $navigationGroup = 'إدارة النظام';


    public static function getNavigationSort(): ?int
    {
        return 8;
    }

    public static function getCauserNameColumnCompoment(): Column
    {
        return parent::getCauserNameColumnCompoment()->sortable();
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()->can('view_custom::activity::log');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Split::make([
                    Forms\Components\Section::make([
                        Forms\Components\TextInput::make('causer_id')
                            ->afterStateHydrated(function ($component, ?\Illuminate\Database\Eloquent\Model $record) {
                                /** @phpstan-ignore-next-line */
                                return $component->state($record->causer?->name);
                            })
                            ->label('المتسبب'),

                        Forms\Components\TextInput::make('subject_type')
                            ->afterStateHydrated(function ($component, ?\Illuminate\Database\Eloquent\Model $record, $state) {
                                /** @var \Spatie\Activitylog\Models\Activity $record */
                                return $state ? $component->state(\Illuminate\Support\Str::of($state)->afterLast('\\')->headline() . ' # ' . $record->subject_id) : '-';
                            })
                            ->label('نوع الموضوع'),

                        Forms\Components\Textarea::make('description')
                            ->label('الوصف')
                            ->rows(2)
                            ->columnSpan('full'),
                    ]),
                    Forms\Components\Section::make([
                        Forms\Components\Placeholder::make('log_name')
                            ->content(function (?Model $record): string {
                                /** @var \Spatie\Activitylog\Models\Activity $record */
                                return $record->log_name ? ucwords($record->log_name) : '-';
                            })
                            ->label('اسم السجل'),

                        Forms\Components\Placeholder::make('event')
                            ->content(function (?Model $record): string {
                                /** @phpstan-ignore-next-line */
                                return $record?->event ? ucwords($record?->event) : '-';
                            })
                            ->label('الحدث'),

                        Forms\Components\Placeholder::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->content(function (?Model $record): string {
                                /** @var \Spatie\Activitylog\Models\Activity $record */
                                return $record->created_at ? "{$record->created_at->format(config('filament-activitylog.datetime_format', 'd/m/Y H:i:s'))}" : '-';
                            }),
                    ])->grow(false),
                ])->from('md'),

                Forms\Components\Section::make()
                    ->columns()
                    ->visible(fn($record) => $record->properties?->count() > 0)
                    ->schema(function (?Model $record) {
                        /** @var \Spatie\Activitylog\Models\Activity $record */
                        $properties = $record->properties->except(['attributes', 'old']);
                        // dd($properties);
                        $schema = [];

                        if ($properties->count()) {
                            $schema[] = Forms\Components\KeyValue::make('properties')
                                ->label('الخصائص')
                                ->columnSpan('full');
                        }

                        if ($old = $record->properties->get('old')) {
                            $schema[] = Forms\Components\KeyValue::make('old')
                                ->formatStateUsing(fn() => self::formatDateValues($old))
                                ->label('القديم');
                        }

                        if ($attributes = $record->properties->get('attributes')) {
                            $schema[] = Forms\Components\KeyValue::make('attributes')
                                ->formatStateUsing(fn() => self::formatDateValues($attributes))
                                ->label('السمات');
                        }


                        if ($properties->isNotEmpty()) {
                            $propertiesSchema = [];
                            foreach ($properties as $key => $value) {
                                if (is_array($value)) {
                                    // Handle first level nested array
                                    $nestedSchema = [];
                                    foreach ($value as $nestedKey => $nestedValue) {
                                        if (is_array($nestedValue)) {
                                            // Handle second level nested array
                                            $nestedSchema[] = Forms\Components\KeyValue::make("properties.{$key}.{$nestedKey}")
                                                ->label($nestedKey)
                                                ->formatStateUsing(fn() => $nestedValue);
                                        }
                                    }

                                    if (!empty($nestedSchema)) {
                                        $propertiesSchema[] = Forms\Components\Section::make($key)
                                            ->schema($nestedSchema)
                                            ->collapsible();
                                    } else {
                                        $propertiesSchema[] = Forms\Components\KeyValue::make("properties.{$key}")
                                            ->label($key)
                                            ->formatStateUsing(fn() => $value);
                                    }
                                } else {
                                    $properties[$key] = $value;
                                }
                            }

                            $schema[] = Forms\Components\Section::make('الخصائص')
                                ->schema($propertiesSchema)
                                ->columnSpan('full');
                        }
                        return $schema;
                    }),
            ])->columns(1);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomActivityLogs::route('/'),
            'view' => ViewCustomActivityLog::route('/{record}'),
        ];
    }


    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
        ];
    }
}
