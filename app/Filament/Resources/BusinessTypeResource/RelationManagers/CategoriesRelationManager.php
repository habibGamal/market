<?php

namespace App\Filament\Resources\BusinessTypeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'categories';

    protected static ?string $title = 'الفئات';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('الاسم')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('image')
                    ->label('الصورة')
                    ->circular(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->multiple()
                    ->label('ربط فئة')
                    ->modalHeading('ربط فئة جديدة')
                    ->modalButton('ربط الفئة'),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label('إلغاء الربط'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label('إلغاء ربط المحدد'),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-square-3-stack-3d')
            ->emptyStateHeading('لا يوجد فئات مرتبطة')
            ->emptyStateDescription('قم بربط الفئات مع نوع النشاط من هنا.')
            ->emptyStateActions([
                Tables\Actions\AttachAction::make()
                    ->label('ربط فئة'),
            ]);
    }
}
