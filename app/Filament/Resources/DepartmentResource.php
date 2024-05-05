<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Filament\Resources\DepartmentResource\RelationManagers;
use App\Models\Department;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Model;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;
    // Update icon in navigation
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    // Update name in navigation
    protected static ?string $navigationLabel = "Department";
    // Update name in resource page
    protected static ?string $modelLabel = "Department";
    // Add resource to navigation group
    protected static ?string $navigationGroup = "System Management";

    protected static ?int $navigationSort = 4;

    // Add navigation badge for Employees Screen
    public static function getNavigationBadge(): ?string {
        return static::getModel()::count();
    }
    // Add navigation badge colour
    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::count() > 5 ?
            "warning" :
            "success";
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make("employees_count")
                    ->counts("employees"),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): InfoList {
        return $infolist
            ->schema([
                Section::make("Department Information")
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('employees_count')
                            ->state(function (Model $record): float {
                                return $record->employees()->count();
                            }),
                    ]) ->columns(2)
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'view' => Pages\ViewDepartment::route('/{record}'),
            'edit' => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }
}
