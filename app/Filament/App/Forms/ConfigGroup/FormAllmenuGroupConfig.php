<?php
namespace App\Filament\App\Forms\ConfigGroup;

use App\Filament\App\Forms\FormConfig;
use App\Models\CoreApp\Company;
use App\Models\CoreApp\Departement;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;

class FormAllmenuGroupConfig
{
    public static function company()
    {
        return [
            TextInput::make('name')
                ->suffixIcon('heroicon-o-building-office')
                ->required(),
            TextInput::make('latitude')
                ->suffixIcon('gmdi-location-pin')
                ->numeric()
                ->required(),
            TextInput::make('longitude')
                ->suffixIcon('gmdi-location-pin')
                ->numeric()
                ->required(),
            TextInput::make('radius')
                ->suffixIcon('gmdi-location-searching-o')
                ->numeric()
                ->required(),
            Textarea::make('full_address')
                ->columnSpanFull()
                ->required(),
        ];
    }

    public static function departement()
    {
        return [
            Select::make('company_id')
                ->relationship('company', 'name')
                ->searchable()
                ->preload()
                ->createOptionForm([
                    Section::make(self::company())
                        ->columns(FormConfig::columns(1, 2, 2, 2))
                ])
                ->editOptionForm([
                    Section::make(self::company())
                        ->columns(FormConfig::columns(1, 2, 2, 2))
                ])
                ->required(),
            TextInput::make('name')->required()
        ];
    }

    public static function job_level()
    {
        return [
            Select::make('company_id')
                ->label('Choose company')
                ->relationship('company', 'name')
                ->createOptionForm([
                    Section::make(self::company())
                        ->columns(FormConfig::columns(1, 2, 2, 2))
                ])
                ->editOptionForm([
                    Section::make(self::company())
                        ->columns(FormConfig::columns(1, 2, 2, 2))
                ])
                ->required(),
            Select::make('departement_id')
                ->label('Choose departement')
                ->relationship('departement', 'name')
                ->createOptionForm([
                    Section::make(self::departement())
                        ->columns(FormConfig::columns(1, 2, 2, 2))
                ])
                ->editOptionForm([
                    Section::make(self::departement())
                        ->columns(FormConfig::columns(1, 2, 2, 2))
                ])
                ->required(),
            TextInput::make('name')
                ->required()
                ->maxLength(255),
        ];
    }

    public static function job_position()
    {
        return [
            Select::make('company_id')
                ->label('Choose company')
                ->relationship('company', 'name')
                ->createOptionForm([
                    Section::make(self::company())
                        ->columns(FormConfig::columns(1, 2, 2, 2))
                ])
                ->editOptionForm([
                    Section::make(self::company())
                        ->columns(FormConfig::columns(1, 2, 2, 2))
                ])
                ->required(),
            Select::make('departement_id')
                ->label('Choose departement')
                ->relationship('departement', 'name')
                ->createOptionForm([
                    Section::make(self::departement())
                        ->columns(FormConfig::columns(1, 2, 2, 2))
                ])
                ->editOptionForm([
                    Section::make(self::departement())
                        ->columns(FormConfig::columns(1, 2, 2, 2))
                ])
                ->required(),
            TextInput::make('name')
                ->required()
                ->maxLength(255),
        ];
    }

    public static function time_work()
    {
        return [
            Select::make('company_id')
                ->label('Choose company')
                ->options(Company::all()->pluck('name', 'id'))
                ->required(),
            Select::make('departemen_id')
                ->label('Choose departement')
                ->options(Departement::all()->pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->required(),
            TextInput::make('name')
                ->required()
                ->maxLength(255),
            TimePicker::make('in')
                ->required(),
            TimePicker::make('out')
                ->required(),
        ];
    }
}
