<?php
namespace App\Filament\App\Forms;

use App\Models\CoreApp\Company;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Wizard;

class FormUserWizard
{
    public static function form()
    {
        return [
            Wizard::make([
                Wizard\Step::make('Account')
                    ->schema([
                        Select::make('company_id')
                            ->options(Company::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                        TextInput::make('nip'),
                        TextInput::make('name'),
                        TextInput::make('email')->email()->unique(table: User::class),
                        TextInput::make('password')->confirmed(),
                        TextInput::make('password_confirmation'),
                        ToggleButtons::make('status')
                            ->label('Set status user')
                            ->options(User::STATUS)
                            ->default('active')
                            ->grouped(),
                        FileUpload::make('avatar')
                            ->avatar()
                            ->image()
                            ->disk(env('FILESYSTEM_DISK'))
                            ->visibility('public')
                            ->directory('avatar-users'),
                    ]),
                Wizard\Step::make('Detail information')
                    ->schema(FormUser::detail()),
                Wizard\Step::make('Address information')
                    ->schema(FormUser::address()),
                Wizard\Step::make('Employe information')
                    ->schema(FormUser::employe()),
                Wizard\Step::make('Family information')
                    ->schema(FormUser::family()),
                Wizard\Step::make('Formal education information')
                    ->schema(FormUser::formal_education()),
                Wizard\Step::make('Informal education information')
                    ->schema(FormUser::informal_education()),
                Wizard\Step::make('Work experience information')
                    ->schema(FormUser::work_experience()),
            ])
                ->columns(FormConfig::columns(1,2,2,2))
        ];
    }
}
