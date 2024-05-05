<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use App\Models\State;
use Filament\Forms\Get;
use App\Models\City;
use Filament\Forms\Set;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Infolist;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Carbon;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Enums\FiltersLayout;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    // Add resource to navigation group
    protected static ?string $navigationGroup = "Employee Management";
    // Add global search by field
    protected static ?string $recordTitleAttribute = "first_name";
    // Add global search result attribute
    public static function getGlobalSearchResultTitle(Model $record): string {
        return $record->last_name;
    }
    // Add attributes to search globally by
    public static function getGloballySearchableAttributes(): array
    {
        return [
            "first_name", "last_name", "middle_name", 
        ];
    }
    // Add global search result details
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            "Country" => $record->country->name,
        ];
    }
    // Add eager-loadin for relationships in global search
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()
            ->with(["country"]);
    }
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
                Forms\Components\Section::make("Relationships")
                    ->schema([
                        Forms\Components\Select::make('country_id')
                            ->relationship(name: "country", titleAttribute: "name")
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(
                                function (Set $set) {
                                    $set("state_id", null);
                                    $set("city_id", null);
                                }
                            )
                            ->required(),
                        Forms\Components\Select::make('state_id')
                            ->options(
                                fn(Get $get): Collection => State::query()
                                    ->where("country_id", $get("country_id"))
                                    ->pluck("name", "id")
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(
                                fn (Set $set) => $set("city_id", null)
                            )
                            ->native(false)
                            ->required(),
                        Forms\Components\Select::make('city_id')
                            ->options(
                                fn(Get $get): Collection => City::query()
                                    ->where("state_id", $get("state_id"))
                                    ->pluck("name", "id")
                            )
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->live()
                            ->required(),
                        Forms\Components\Select::make('department_id')
                            ->relationship(name: "department", titleAttribute: "name")
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->required(),
                    ])->columns(2),
                Forms\Components\Section::make("User Name")
                    ->description("Put the user name details here")
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('middle_name')
                            ->required()
                            ->maxLength(255),
                    ])->columns(3),
                Forms\Components\Section::make("User Address")
                    ->schema([
                        Forms\Components\TextInput::make('address')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('zip_code')
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),
                Forms\Components\Section::make("Dates")
                    ->schema([
                        Forms\Components\DatePicker::make('date_of_birth')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->required(),
                        Forms\Components\DatePicker::make('date_hired')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->required(),
                    ])->columns(2),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('country.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('state.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('city.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('department.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('middle_name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('address')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('zip_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('date_hired')
                    ->date()
                    ->sortable(),
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
                SelectFilter::make("Department")
                    ->relationship("department", "name")
                    ->searchable()
                    ->preload()
                    ->label("Filter by Department")
                    ->indicator("Department"),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query
                                    ->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query
                                    ->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                 
                        if ($data['created_from'] ?? null) {
                            $indicators[] = Indicator::make(
                                    'Created from ' . Carbon::parse($data['created_from'])
                                        ->toFormattedDateString()
                                )
                                ->removeField('created_from');
                        }
                 
                        if ($data['created_until'] ?? null) {
                            $indicators[] = Indicator::make(
                                    'Created until ' . Carbon::parse($data['created_until'])
                                        ->toFormattedDateString()
                                )
                                ->removeField('created_until');
                        }
                 
                        return $indicators;
                    })->columnSpan(2)->columns(2),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->successNotificationTitle(
                        "Employee deleted."
                    ),
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
                Section::make("Relationships")
                    ->schema([
                        TextEntry::make('country.name'),
                        TextEntry::make('state.name'),
                        TextEntry::make('city.name'),
                        TextEntry::make('department.name'),
                    ]) ->columns(2),
                Section::make("Name")
                    ->schema([
                        TextEntry::make('first_name'),
                        TextEntry::make('middle_name'),
                        TextEntry::make('last_name'),
                    ]) ->columns(3),
                Section::make("Address")
                    ->schema([
                        TextEntry::make('address'),
                        TextEntry::make('zip_code'),
                    ]) ->columns(2),
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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'view' => Pages\ViewEmployee::route('/{record}'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
