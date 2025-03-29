<?php

namespace App\Filament\Pages\App;

use App\Enums\SettingKey;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
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
            // If the setting type is boolean, cast it to true/false
            if ($this->getSettingType($setting->key) === 'boolean') {
                $this->data[$setting->key] = (bool) $setting->value;
            }
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

                                    Toggle::make(SettingKey::STOP_SELLING->value)
                                        ->label(SettingKey::STOP_SELLING->getLabel())
                                        ->helperText('عند تفعيل هذا الخيار سيتم إيقاف البيع في التطبيق')
                                        ->onColor('danger')
                                ]),

                                RichEditor::make(SettingKey::ORDER_RECEIPT_FOOTER->value)
                                    ->label(SettingKey::ORDER_RECEIPT_FOOTER->getLabel())
                                    ->placeholder('أدخل نص تذييل قسيمة الطلب')
                                    ->helperText('هذا النص سوف يظهر في أسفل قسيمة الطلب المطبوعة')
                                    ->columnSpanFull()
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'underline',
                                        'strike',
                                        'bulletList',
                                        'orderedList',
                                        'alignLeft',
                                        'alignCenter',
                                        'alignRight',
                                    ]),
                            ]),

                        // Support Settings Tab
                        Tabs\Tab::make('support')
                            ->label('إعدادات الدعم')
                            ->icon('heroicon-o-lifebuoy')
                            ->schema([
                                Section::make('معلومات الاتصال')
                                    ->description('معلومات الاتصال التي ستظهر للعملاء في صفحة الدعم')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextInput::make(SettingKey::SUPPORT_PHONE->value)
                                                ->label(SettingKey::SUPPORT_PHONE->getLabel())
                                                ->tel()
                                                ->placeholder('01234567890')
                                                ->required(),

                                            TextInput::make(SettingKey::SUPPORT_EMAIL->value)
                                                ->label(SettingKey::SUPPORT_EMAIL->getLabel())
                                                ->email()
                                                ->placeholder('support@yourdomain.com')
                                                ->required(),

                                            TextInput::make(SettingKey::SUPPORT_HOURS->value)
                                                ->label(SettingKey::SUPPORT_HOURS->getLabel())
                                                ->placeholder('من الأحد إلى الخميس، 9 صباحاً - 5 مساءً')
                                                ->required(),

                                            TextInput::make(SettingKey::SUPPORT_CHAT_HOURS->value)
                                                ->label(SettingKey::SUPPORT_CHAT_HOURS->getLabel())
                                                ->placeholder('متاحة من 9 صباحاً - 9 مساءً')
                                                ->required(),
                                        ]),

                                        TextInput::make(SettingKey::SUPPORT_ADDRESS->value)
                                            ->label(SettingKey::SUPPORT_ADDRESS->getLabel())
                                            ->placeholder('المبنى 123، شارع الرئيسي، المدينة')
                                            ->required()
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('سياسات الموقع')
                                    ->description('سياسات الموقع التي ستظهر للعملاء في صفحة الدعم')
                                    ->schema([
                                        RichEditor::make(SettingKey::PRIVACY_POLICY->value)
                                            ->label(SettingKey::PRIVACY_POLICY->getLabel())
                                            ->placeholder('أدخل نص سياسة الخصوصية')
                                            ->columnSpanFull()
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'underline',
                                                'strike',
                                                'bulletList',
                                                'orderedList',
                                                'heading',
                                                'h2',
                                                'h3',
                                                'alignLeft',
                                                'alignCenter',
                                                'alignRight',
                                            ]),

                                        RichEditor::make(SettingKey::SHIPPING_POLICY->value)
                                            ->label(SettingKey::SHIPPING_POLICY->getLabel())
                                            ->placeholder('أدخل نص سياسة الشحن والتوصيل')
                                            ->columnSpanFull()
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'underline',
                                                'strike',
                                                'bulletList',
                                                'orderedList',
                                                'heading',
                                                'h2',
                                                'h3',
                                                'alignLeft',
                                                'alignCenter',
                                                'alignRight',
                                            ]),

                                        RichEditor::make(SettingKey::RETURN_POLICY->value)
                                            ->label(SettingKey::RETURN_POLICY->getLabel())
                                            ->placeholder('أدخل نص سياسة الإرجاع والاستبدال')
                                            ->columnSpanFull()
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'underline',
                                                'strike',
                                                'bulletList',
                                                'orderedList',
                                                'heading',
                                                'h2',
                                                'h3',
                                                'alignLeft',
                                                'alignCenter',
                                                'alignRight',
                                            ]),

                                        RichEditor::make(SettingKey::PAYMENT_POLICY->value)
                                            ->label(SettingKey::PAYMENT_POLICY->getLabel())
                                            ->placeholder('أدخل نص سياسة الدفع')
                                            ->columnSpanFull()
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'underline',
                                                'strike',
                                                'bulletList',
                                                'orderedList',
                                                'heading',
                                                'h2',
                                                'h3',
                                                'alignLeft',
                                                'alignCenter',
                                                'alignRight',
                                            ]),
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

                        // Integration Settings Tab
                        Tabs\Tab::make('integration')
                            ->label('إعدادات التكامل')
                            ->icon('heroicon-o-link')
                            ->schema([
                                Grid::make(1)->schema([
                                    TextInput::make(SettingKey::WHATSAPP_SERVER_ENDPOINT->value)
                                        ->label(SettingKey::WHATSAPP_SERVER_ENDPOINT->getLabel())
                                        ->placeholder('أدخل رابط خادم واتساب')
                                        ->helperText('أدخل رابط خادم واتساب المستخدم في إرسال الرسائل')
                                        ->url()
                                        ->required(),
                                ]),
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
