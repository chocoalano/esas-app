<?php
namespace App\Filament\App\Forms;

use App\Models\CoreApp\Departement;
use App\Models\CoreApp\JobLevel;
use App\Models\CoreApp\JobPosition;
use App\Models\User;
use App\Models\UserApp\UserAddress;
use App\Models\UserApp\UserDetail;
use App\Models\UserApp\UserFamily;
use App\Models\UserApp\UserFormalEducation;
use App\Models\UserApp\UserInformalEducation;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;

class FormUser
{
    public static function form()
    {
        return [
            Tabs::make('Tabs')
                ->tabs([
                    Tabs\Tab::make('Account')
                        ->icon('heroicon-o-user-circle')
                        ->schema(self::account())
                        ->columns(FormConfig::columns(1, 2, 4, 4)),
                    Tabs\Tab::make('Detail')
                        ->icon('heroicon-o-information-circle')
                        ->columns(FormConfig::columns(1, 2, 3, 3))
                        ->schema(self::detail()),
                    Tabs\Tab::make('Address')
                        ->icon('gmdi-location-pin')
                        ->columns(FormConfig::columns(1, 2, 4, 4))
                        ->schema(self::address()),
                    Tabs\Tab::make('Employe')
                        ->icon('gmdi-work')
                        ->columns(FormConfig::columns(1, 2, 4, 4))
                        ->schema(self::employe()),
                    Tabs\Tab::make('Family')
                        ->icon('gmdi-family-restroom-o')
                        ->schema(self::family()),
                    Tabs\Tab::make('Formal education')
                        ->icon('gmdi-school')
                        ->schema(self::formal_education()),
                    Tabs\Tab::make('Informal education')
                        ->icon('gmdi-school-o')
                        ->schema(self::informal_education()),
                    Tabs\Tab::make('Work experience')
                        ->icon('gmdi-work-history-r')
                        ->schema(self::work_experience()),
                ])
                ->columnSpanFull()
                ->activeTab(1),
        ];
    }

    public static function account()
    {
        return [
            Select::make('company_id')
                ->label('company')
                ->relationship(name: 'company', titleAttribute: 'name')
                ->searchable()
                ->preload()
                ->required(),
            Select::make('role')
                ->options(Role::all()->pluck('name', 'name'))
                ->preload()
                ->multiple()
                ->searchable()
                ->required(),
            TextInput::make('nip')
                ->unique(
                    User::class, // Tabel atau model
                    'nip',       // Kolom yang divalidasi
                    ignoreRecord: true
                )
                ->required(),
            TextInput::make('name')->required(),
            TextInput::make('email')
                ->email()
                ->unique(
                    User::class, // Tabel atau model
                    'email',     // Kolom yang divalidasi
                    ignoreRecord: true // Abaikan record saat update
                )
                ->required(),
            TextInput::make('password')->confirmed(),
            TextInput::make('password_confirmation'),
            ToggleButtons::make('status')
                ->label('Set status user')
                ->options(User::STATUS)
                ->default('active')
                ->grouped()
                ->required(),
            FileUpload::make('avatar')
                ->avatar()
                ->image()
                ->disk(env('FILESYSTEM_DISK'))
                ->directory('avatar-users')
                ->visibility('public'),
        ];
    }

    public static function detail()
    {
        return [
            TextInput::make('phone')->numeric()->tel()->required(),
            TextInput::make('placebirth')->required(),
            DatePicker::make('datebirth')->required(),
            ToggleButtons::make('gender')->options(UserDetail::GENDER)->grouped()->required(),
            ToggleButtons::make('blood')->options(UserDetail::BLOOD_TYPE)->grouped()->required(),
            ToggleButtons::make('marital_status')->options(UserDetail::MARITAL_STATUS)->grouped()->required(),
            Select::make('religion')->options(UserDetail::RELIGION)->required(),
        ];
    }

    public static function address()
    {
        return [
            ToggleButtons::make('identity_type')->options(UserAddress::IDENTITYY_TYPE)->grouped()->required(),
            TextInput::make('identity_numbers')->numeric()->required(),
            TextInput::make('province')->required(),
            TextInput::make('city')->required(),
            Textarea::make('citizen_address')->columnSpan(2)->required(),
            Textarea::make('residential_address')->columnSpan(2)->required(),
        ];
    }

    public static function employe()
    {
        return [
            Select::make('departement_id')
                ->options(Departement::all()->pluck('name', 'id'))->required()
                ->searchable(),
            Select::make('job_position_id')
                ->options(JobPosition::all()->pluck('name', 'id'))->required()
                ->searchable(),
            Select::make('job_level_id')
                ->options(JobLevel::all()->pluck('name', 'id'))->required()
                ->searchable(),
            Select::make('approval_line_id')
                ->options(User::all()->pluck('name', 'id'))->required()
                ->searchable(),
            Select::make('approval_manager_id')
                ->options(User::all()->pluck('name', 'id'))->required()
                ->searchable(),
            DatePicker::make('join_date')->default(Carbon::now())->required(),
            DatePicker::make('sign_date')->default(Carbon::now())->required(),
            DatePicker::make('resign_date'),
            TextInput::make('bank_name')->required(),
            TextInput::make('bank_number')->numeric()->required(),
            TextInput::make('bank_holder')->required(),
        ];
    }

    public static function family()
    {
        return [
            Repeater::make('family')
                ->schema([
                    TextInput::make('fullname'),
                    ToggleButtons::make('relationship')->options(UserFamily::RELATIONSHIP),
                    DatePicker::make('birthdate'),
                    ToggleButtons::make('marital_status')->options(UserFamily::MARITAL_STATUS),
                    TextInput::make('job'),
                ])
                ->columnSpanFull()
                ->columns([
                    'sm' => 1,
                    'xl' => 3,
                    '2xl' => 3,
                ]),
        ];
    }

    public static function formal_education()
    {
        return [
            Repeater::make('formal_education')
                ->label('Formal education information')
                ->schema([
                    TextInput::make('institution'),
                    Select::make('majors')->options(UserFormalEducation::MAJORS),
                    TextInput::make('score')->numeric(),
                    DatePicker::make('start')->default(Carbon::now()),
                    DatePicker::make('finish')->default(Carbon::now()),
                    ToggleButtons::make('status')->options(UserFormalEducation::STATUS),
                    ToggleButtons::make('certification')->boolean()->grouped(),
                ])
                ->columnSpanFull()
                ->columns([
                    'sm' => 1,
                    'xl' => 4,
                    '2xl' => 4,
                ]),
        ];
    }

    public static function informal_education()
    {
        return [
            Repeater::make('informal_education')
                ->label('Informal education information')
                ->schema([
                    TextInput::make('institution'),
                    DatePicker::make('start')->default(Carbon::now()),
                    DatePicker::make('finish')->default(Carbon::now()),
                    ToggleButtons::make('type')->options(UserInformalEducation::TYPE)->grouped(),
                    ToggleButtons::make('status')->options(UserInformalEducation::STATUS)->grouped(),
                    ToggleButtons::make('certification')->boolean()->grouped(),
                ])
                ->columnSpanFull()
                ->columns([
                    'sm' => 1,
                    'xl' => 3,
                    '2xl' => 3,
                ]),
        ];
    }

    public static function work_experience()
    {
        return [
            Repeater::make('work_experience')
                ->label('Work experience information')
                ->schema([
                    TextInput::make('company_name'),
                    DatePicker::make('start')->default(Carbon::now()),
                    DatePicker::make('finish')->default(Carbon::now()),
                    TextInput::make('position'),
                    ToggleButtons::make('status')->options(UserInformalEducation::STATUS),
                    ToggleButtons::make('certification')->boolean()->grouped(),
                ])
                ->columnSpanFull()
                ->columns([
                    'sm' => 1,
                    'xl' => 3,
                    '2xl' => 3,
                ]),
        ];
    }
}
