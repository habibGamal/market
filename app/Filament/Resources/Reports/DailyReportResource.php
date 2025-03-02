<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Resources\Reports\DailyReportResource\Pages;
use App\Filament\Resources\Reports\DailyReportResource\Widgets;
use App\Models\ExpenseType;
use App\Services\Reports\DailyReportService;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DailyReportResource extends Resource
{
    protected static ?string $model = ExpenseType::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'التقارير';

    protected static ?string $modelLabel = 'التقرير اليومي';

    protected static ?string $pluralModelLabel = 'التقرير اليومي';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table->paginated(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDailyReports::route('/'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
        ];
    }
}
