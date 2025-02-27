<?php
namespace App\Filament\App\Forms\Attendance;

use App\Filament\App\Forms\ConfigGroup\FormAllmenuGroupConfig;
use App\Filament\App\Forms\FormConfig;
use App\Models\AdministrationApp\UserAttendance;
use App\Models\AdministrationApp\UserTimeworkSchedule;
use App\Models\CoreApp\Company;
use App\Models\CoreApp\Departement;
use App\Models\CoreApp\TimeWork;
use App\Models\User;
use App\Repositories\Interfaces\CoreApp\TimeWorkInterface;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Model;
class FormAttendance
{
    public static function form()
    {
        return [
            Select::make('user_id')
                ->relationship('user', 'name')
                ->preload()
                ->searchable()
                ->live()
                ->required(),
            Select::make('user_timework_schedule_id')
                ->options(function (Get $get) {
                    $userId = $get('user_id');
                    if ($userId) {
                        return UserTimeworkSchedule::where('user_id', $userId)
                            ->get()
                            ->pluck('work_day', 'id');
                    }
                    return [];
                })
                ->preload()
                ->searchable()
                ->required(),
            TextInput::make('lat_in')
                ->required()
                ->maxLength(100),
            TextInput::make('lat_out')
                ->required()
                ->maxLength(100),
            TextInput::make('long_in')
                ->required()
                ->maxLength(100),
            TextInput::make('long_out')
                ->required()
                ->maxLength(100),
            TimePicker::make('time_in')
                ->label('Select Time In')
                ->withoutSeconds()
                ->native(false)
                ->format('H:i')
                ->displayFormat('H:i')
                ->closeOnDateSelection()
                ->required(),
            TimePicker::make('time_out')
                ->label('Select Time Out')
                ->withoutSeconds()
                ->native(false)
                ->format('H:i')
                ->displayFormat('H:i')
                ->closeOnDateSelection()
                ->required(),
            ToggleButtons::make('status_in')
                ->options(UserAttendance::STATUS)
                ->grouped()
                ->required(),
            ToggleButtons::make('status_out')
                ->options(UserAttendance::STATUS)
                ->grouped()
                ->required(),
            FileUpload::make('image_in')
                ->image()
                ->avatar()
                ->disk(env('FILESYSTEM_DISK'))
                ->directory('attendance-in')
                ->visibility('public')
                ->required(),
            FileUpload::make('image_out')
                ->image()
                ->avatar()
                ->disk(env('FILESYSTEM_DISK'))
                ->directory('attendance-out')
                ->visibility('public')
                ->required(),
        ];
    }

    public static function presence()
    {
        return [
            TextInput::make('lat')
                ->required()
                ->maxLength(100),
            TextInput::make('long')
                ->required()
                ->maxLength(100),
            ToggleButtons::make('type')
                ->options([
                    'in' => 'In',
                    'out' => 'Out',
                ])
                ->grouped()
                ->required(),
            FileUpload::make('image')
                ->image()
                ->avatar()
                ->disk(env('FILESYSTEM_DISK'))
                ->directory('presence')
                ->required(),
        ];
    }

    public static function timework_schedule() // Pastikan model disuntikkan
    {
        return [
            Select::make('company_id')
                ->label('Choose company')
                ->options(Company::all()->pluck('name', 'id'))
                ->preload()
                ->live()
                ->required(),
            Select::make('departement')
                ->label('Choose departement')
                ->options(function (Get $get) {
                    $companyId = $get('company_id');
                    if ($companyId) {
                        return Departement::where('company_id', $companyId)
                            ->get()
                            ->pluck('name', 'id');
                    }
                    return [];
                })
                ->visible(fn(Get $get) => $get('company_id') !== null ? true : false)
                ->preload()
                ->searchable()
                ->live()
                ->required(),
            Select::make('user_id')
                ->label('Choose users')
                ->options(function (Get $get) {
                    $companyId = $get('company_id');
                    $departementId = $get('departement');
                    if ($companyId && $departementId) {
                        return User::where('company_id', $companyId)
                            ->whereHas('employee', function ($query) use ($departementId) {
                                $query->where('departement_id', $departementId);
                            })
                            ->get()
                            ->pluck('name', 'id');
                    }
                    return [];
                })
                ->preload()
                ->searchable()
                ->multiple()
                ->visible(fn(Get $get) => $get('company_id') !== null && $get('departement') !== null ? true : false)
                ->required(),
            Select::make('time_work_id')
                ->label('Choose time')
                ->options(function (Get $get) {
                    $companyId = $get('company_id');
                    $departementId = $get('departement');
                    if ($companyId && $departementId) {
                        return TimeWork::where('company_id', $companyId)
                            ->where('departemen_id', $departementId)
                            ->get()
                            ->pluck('name', 'id');
                    }
                    return [];
                })
                ->createOptionForm([
                    Section::make(FormAllmenuGroupConfig::time_work())
                        ->columns(FormConfig::columns(1, 2, 4, 4))
                ])
                ->createOptionUsing(function (array $data, TimeWorkInterface $service): Model {
                    return $service->create($data);
                })
                ->visible(fn(Get $get) => $get('company_id') !== null && $get('departement') !== null && $get('user_id') !== null ? true : false)
                ->required(),
            DatePicker::make('work_day_start')
                ->before('work_day_finish')
                ->minDate(now()->startOfYear())
                ->maxDate(now()->endOfYear())
                ->visible(fn(Get $get) => $get('company_id') !== null && $get('departement') !== null && $get('user_id') !== null ? true : false)
                ->required(),
            DatePicker::make('work_day_finish')
                ->after('work_day_start')
                ->minDate(now()->startOfYear())
                ->maxDate(now()->endOfYear())
                ->visible(fn(Get $get) => $get('company_id') !== null && $get('departement') !== null && $get('user_id') !== null ? true : false)
                ->required(),
            Select::make('dayoff')
                ->options([
                    'Monday' => 'Minggu',
                    'Tuesday' => 'Senin',
                    'Wednesday' => 'Selasa',
                    'Thursday' => 'Rabu',
                    'Friday' => 'Kamis',
                    'Saturday' => 'Jumat',
                    'Sunday' => 'Sabtu',
                ])
                ->multiple()
                ->visible(fn(Get $get) => $get('company_id') !== null && $get('departement') !== null && $get('user_id') !== null ? true : false)
                ->required()
        ];
    }
}
