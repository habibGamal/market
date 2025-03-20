<?php

namespace App\Filament\Pages\App;

use App\Enums\SettingKey;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;

class Settings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'النظام';
    protected static ?string $title = 'إعدادات النظام';
    protected static ?string $slug = 'settings';
    protected static string $view = 'filament.pages.settings';
    protected static ?int $navigationSort = 100;

    public ?array $data = [];

    public function mount(): void
    {
        $settings = Setting::all();

        foreach ($settings as $setting) {
            $this->data[$setting->key] = $setting->value;
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Settings')
                    ->tabs([
                        // System Settings Tab
                        Tabs\Tab::make('system')
                            ->label('إعدادات النظام')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make(SettingKey::APP_NAME->value)
                                        ->label(SettingKey::APP_NAME->getLabel())
                                        ->required(),

                                    FileUpload::make(SettingKey::APP_ICON->value)
                                        ->label(SettingKey::APP_ICON->getLabel())
                                        ->image()
                                        ->imageEditor()
                                        ->directory('settings/system')
                                        ->visibility('public')
                                        ->downloadable()
                                        ->columnSpanFull(),

                                    FileUpload::make(SettingKey::APP_LOGO->value)
                                        ->label(SettingKey::APP_LOGO->getLabel())
                                        ->image()
                                        ->imageEditor()
                                        ->directory('settings/system')
                                        ->visibility('public')
                                        ->downloadable()
                                        ->columnSpanFull(),
                                ]),
                            ]),

                        // Orders Settings Tab
                        Tabs\Tab::make('orders')
                            ->label('إعدادات الطلبات')
                            ->icon('heroicon-o-shopping-cart')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make(SettingKey::MIN_TOTAL_ORDER->value)
                                        ->label(SettingKey::MIN_TOTAL_ORDER->getLabel())
                                        ->numeric()
                                        ->minValue(0)
                                        ->step(0.01)
                                        ->required(),

                                    TextInput::make(SettingKey::RATING_POINTS_PERCENT->value)
                                        ->label(SettingKey::RATING_POINTS_PERCENT->getLabel())
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(100)
                                        ->step(0.01)
                                        ->suffix('%')
                                        ->required(),
                                ]),
                            ]),

                        // Invoices Settings Tab (Empty for now)
                        Tabs\Tab::make('invoices')
                            ->label('إعدادات الفواتير')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                // Empty state message
                                Section::make()
                                    ->description('لم يتم إضافة إعدادات للفواتير بعد')
                                    ->icon('heroicon-o-information-circle'),
                            ]),

                        // Notifications Settings Tab (Empty for now)
                        Tabs\Tab::make('notifications')
                            ->label('إعدادات الإشعارات')
                            ->icon('heroicon-o-bell')
                            ->schema([
                                // Empty state message
                                Section::make()
                                    ->description('لم يتم إضافة إعدادات للإشعارات بعد')
                                    ->icon('heroicon-o-information-circle'),
                            ]),
                    ])
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            $settingType = $this->getSettingType($key);

            settings()->set($key, $value, $settingType);
        }


        Notification::make()
            ->title('تم حفظ الإعدادات بنجاح')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('حفظ الإعدادات')
                ->action(fn () => $this->save())
                ->keyBindings(['command+s', 'ctrl+s'])
        ];
    }

    private function getSettingType(string $key): string
    {
        foreach (SettingKey::cases() as $settingKey) {
            if ($settingKey->value === $key) {
                return $settingKey->getType();
            }
        }

        return 'string';
    }
}
