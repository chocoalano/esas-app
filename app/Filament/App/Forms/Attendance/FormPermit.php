<?php
namespace App\Filament\App\Forms\Attendance;

use App\Models\AdministrationApp\PermitType;
use App\Models\AdministrationApp\UserTimeworkSchedule;
use App\Models\CoreApp\TimeWork;
use App\Models\User;
use App\Repositories\Interfaces\AdministrationApp\PermitInterface;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class FormPermit
{
    public static function form()
    {
        return [
            Select::make('user_id')
                ->relationship('user', 'name')
                ->extraInputAttributes(['readonly' => auth()->user()->hasAnyRole(['super_admin', 'Administrator']) ? false : true])
                ->searchable()
                ->preload()
                ->required(),
            Select::make('permit_type_id')
                ->relationship('permitType', 'type')
                ->searchable()
                ->preload()
                ->live()
                ->afterStateUpdated(function (Set $set, ?string $state) {
                    if (!$state) {
                        return;
                    }
                    $process = app(PermitInterface::class);
                    $unique_code = $process->generate_unique_numbers($state);
                    $set('permit_numbers', $unique_code);
                })
                ->required(),
            TextInput::make('permit_numbers')
                ->unique('permits', 'permit_numbers')
                ->required(),
            Select::make('user_timework_schedule_id')
                ->relationship(
                    name: 'userTimeworkSchedule',
                    titleAttribute: 'work_day',
                    modifyQueryUsing: function (Builder $query, Get $get) {
                        $query->where(function ($q) use ($get) {
                            $q
                                ->where('user_id', $get('user_id'))
                                ->whereYear('work_day', Carbon::now()->year)
                                ->whereMonth('work_day', Carbon::now()->month);
                        });
                    }
                )
                ->searchable()
                ->preload()
                ->live()
                ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                    $permitType = PermitType::find($get('permit_type_id'));

                    if ($permitType) {
                        if ($permitType->type === 'izin perubahan jam kerja') {
                            $currentShift = UserTimeworkSchedule::with('timework')->find($state);
                            if ($currentShift && $currentShift->timework) {
                                $set('current_shift_id', $currentShift->timework->id);
                            }
                        } elseif ($permitType->type !== 'izin koreksi absen' && $permitType->type !== 'izin perubahan jam kerja') {
                            $currentShift = UserTimeworkSchedule::find($state);
                            if ($currentShift) {
                                $set('start_date', $currentShift->work_day);
                            }
                        }
                    }
                })
                ->required(),
            TimePicker::make('timein_adjust')
                ->visible(fn(Get $get) => self::isVisiblePermitTypeKoreksiAbsen($get('permit_type_id'))),
            TimePicker::make('timeout_adjust')
                ->visible(fn(Get $get) => self::isVisiblePermitTypeKoreksiAbsen($get('permit_type_id'))),
            Select::make('current_shift_id')
                ->options(function (Get $get) {
                    if ($get('user_id')) {
                        $user = User::find($get('user_id'));
                        if ($user && $user->employee) {
                            return TimeWork::where([
                                'company_id' => $user->company_id,
                                'departemen_id' => $user->employee->departement_id,
                            ])->pluck('name', 'id')->toArray();
                        }
                    }
                    return [];
                })
                ->visible(fn(Get $get) => self::isVisiblePermitTypeChangeShift($get('permit_type_id'))),

            Select::make('adjust_shift_id')
                ->options(function (Get $get) {
                    if ($get('user_id')) {
                        $user = User::find($get('user_id'));
                        if ($user && $user->employee) {
                            return TimeWork::where([
                                'company_id' => $user->company_id,
                                'departemen_id' => $user->employee->departement_id,
                            ])->pluck('name', 'id')->toArray();
                        }
                    }
                    return [];
                })
                ->visible(fn(Get $get) => self::isVisiblePermitTypeChangeShift($get('permit_type_id'))),

            DatePicker::make('start_date')
                ->readOnly()
                ->before('end_date')
                ->visible(fn(Get $get) => self::isVisiblePermitType($get('permit_type_id'))),
            DatePicker::make('end_date')
                ->after('start_date')
                ->visible(fn(Get $get) => self::isVisiblePermitType($get('permit_type_id'))),
            TimePicker::make('start_time')
                ->visible(fn(Get $get) => self::isVisiblePermitType($get('permit_type_id'))),
            TimePicker::make('end_time')
                ->visible(fn(Get $get) => self::isVisiblePermitType($get('permit_type_id'))),
            Textarea::make('notes')
                ->columnSpanFull(),
            FileUpload::make('file')
                ->disk(env('FILESYSTEM_DISK'))
                ->directory('permit-attachments')
                ->visible(fn(Get $get) => self::isVisiblePermitTypeWithFile($get('permit_type_id')))
                ->required()
                ->columnSpanFull(),
        ];
    }
    public static function isVisiblePermitTypeChangeShift($permit_type_id): bool
    {
        if (!$permit_type_id) {
            return false;
        }
        $permit_type = PermitType::find($permit_type_id);
        if (!$permit_type) {
            return false;
        }
        return $permit_type->type === 'izin perubahan jam kerja';
    }
    public static function isVisiblePermitTypeKoreksiAbsen($permit_type_id): bool
    {
        if (!$permit_type_id) {
            return false;
        }
        $permit_type = PermitType::find($permit_type_id);
        if (!$permit_type) {
            return false;
        }
        return $permit_type->type === 'izin koreksi absen';
    }
    public static function isVisiblePermitType($permit_type_id): bool
    {
        if (!$permit_type_id) {
            return false;
        }
        $permit_type = PermitType::find($permit_type_id);
        if (!$permit_type) {
            return false;
        }
        if (!in_array($permit_type->type, ['izin koreksi absen', 'izin perubahan jam kerja'])) {
            return true;
        } else {
            return false;
        }
    }
    public static function isVisiblePermitTypeWithFile($permit_type_id): bool
    {
        if (!$permit_type_id) {
            return false;
        }
        $permit_type = PermitType::find($permit_type_id);
        if (!$permit_type) {
            return false;
        }
        return $permit_type->with_file;
    }
    public static function type()
    {
        return [
            TextInput::make('type')
                ->required()
                ->maxLength(100)
                ->columnSpanFull(),
            Toggle::make('is_payed')
                ->inline(false)
                ->required(),
            Toggle::make('approve_line')
                ->inline(false)
                ->required(),
            Toggle::make('approve_manager')
                ->inline(false)
                ->required(),
            Toggle::make('approve_hr')
                ->inline(false)
                ->required(),
            Toggle::make('with_file')
                ->inline(false)
                ->required(),
            Toggle::make('show_mobile')
                ->inline(false)
                ->required(),
        ];
    }
}
