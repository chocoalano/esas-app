<?php

namespace App\Filament\App\Resources\UserTimeworkScheduleResource\Pages;

use App\Filament\App\Resources\UserTimeworkScheduleResource;
use App\Filament\Exports\AttendanceScheduleExporter;
use App\Models\CoreApp\Company;
use App\Models\CoreApp\Departement;
use App\Repositories\Interfaces\AdministrationApp\ScheduleAttendanceInterface;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Jobs\InsertUpdateScheduleJob;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Filament\Forms\Components\Select;

class ManageUserTimeworkSchedules extends ManageRecords
{
    protected static string $resource = UserTimeworkScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create Schedule Attendance')
                ->icon('gmdi-edit-calendar-o')
                ->using(function (array $data) {
                    if (empty($data['user_id']) || empty($data['time_work_id']) || empty($data['work_day_start']) || empty($data['work_day_finish'])) {
                        throw new \Exception('Data tidak lengkap. Pastikan semua data sudah diisi.');
                    }

                    // Konversi tanggal mulai dan selesai
                    $workDayStart = Carbon::parse($data['work_day_start'])->timezone(config('app.timezone'));
                    $workDayFinish = Carbon::parse($data['work_day_finish'])->timezone(config('app.timezone'));

                    $jadwal = []; // Menyimpan jadwal yang akan dimasukkan
                    $skipDays = $data['dayoff'] ?? []; // Hari yang dilewati
        
                    // Iterasi tanggal
                    while ($workDayStart <= $workDayFinish) {
                        if (!in_array($workDayStart->format('l'), $skipDays)) {
                            // Tambahkan data untuk setiap user_id
                            $jadwal = array_merge($jadwal, array_map(function ($userId) use ($workDayStart, $data) {
                                return [
                                    'user_id' => $userId,
                                    'time_work_id' => $data['time_work_id'],
                                    'work_day' => $workDayStart->toDateString(),
                                ];
                            }, $data['user_id']));
                        }
                        $workDayStart->addDay();
                    }

                    // Kirim data ke queue
                    if (!empty($jadwal)) {
                        InsertUpdateScheduleJob::dispatchSync($jadwal);
                    }
                    return null;
                }),
            Actions\Action::make('template')
                ->visible(fn() => auth()->user()->can('import_user::timework::schedule'))
                ->label('Template import')
                ->outlined()
                ->icon('bi-filetype-xlsx')
                ->color('warning')
                ->form([
                    Select::make('company_id')
                        ->label('Choose company')
                        ->options(Company::all()->pluck('name', 'id'))
                        ->searchable(),
                    Select::make('departement_id')
                        ->label('Choose departement')
                        ->options(Departement::all()->pluck('name', 'id'))
                        ->searchable()
                ])
                ->action(function (array $data): void {
                    redirect()->route('template.schedule', $data);
                }),
            Actions\Action::make('import')
                ->visible(fn() => auth()->user()->can('import_user::timework::schedule'))
                ->label('Import Data')
                ->outlined()
                ->icon('gmdi-import-export-o')
                ->color('info')
                ->form([
                    FileUpload::make('template')
                        ->label('Template Import Schedule')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->maxSize(10240) // Maksimum ukuran file 10MB
                        ->rules(['required', 'mimes:xlsx', 'max:10240']) // Validasi file
                        ->helperText('Only .xlsx files are allowed. Maximum size: 10MB.')
                ])
                ->action(function (array $data): void {
                    // Validasi bahwa file diunggah
                    if (!isset($data['template'])) {
                        throw ValidationException::withMessages([
                            'template' => 'The template file is required.',
                        ]);
                    }
                    $file = storage_path("app/public/" . $data['template']);
                    try {
                        $spreadsheet = IOFactory::load($file);
                        $sheet = $spreadsheet->getSheetByName('Main Data');
                        $rows = $sheet->toArray(null, true, true, true);
                        // Ambil header dari baris pertama
                        $header = array_shift($rows);

                        // Validasi header (opsional)
                        if (!$header || count($header) === 0) {
                            throw ValidationException::withMessages([
                                'template' => 'The uploaded file does not contain a valid header row.',
                            ]);
                        }

                        // Format data menjadi array dengan header sebagai key
                        $formattedData = array_map(function ($row) use ($header) {
                            return array_combine($header, $row);
                        }, $rows);

                        // Debug (untuk melihat hasil array)
                        // dd($formattedData);
        
                        // Lanjutkan proses (misalnya, simpan ke database)
                        $scheduleAttendanceRepository = app(ScheduleAttendanceInterface::class);
                        $import = $scheduleAttendanceRepository->import($formattedData);
                        if ($import) {
                            Notification::make()
                                ->title('Import successfully')
                                ->body('The data is successfully queued for execution, please wait until the process is complete.')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Import unsuccessfully')
                                ->body('Data failed to enter the queue to be executed, take a look at your data, the system has given you a reference to make this import process! make sure all the data that you input has been registered in the system!')
                                ->danger()
                                ->send();
                        }
                    } finally {
                        if (file_exists($file)) {
                            unlink($file);
                        }
                    }
                }),
            Actions\ExportAction::make()
                ->visible(fn() => auth()->user()->can('export_user::timework::schedule'))
                ->label('Export data current month')
                ->outlined()
                ->icon('gmdi-import-export-o')
                ->color('info')
                ->exporter(AttendanceScheduleExporter::class)
                ->modifyQueryUsing(
                    fn(Builder $query) => $query
                        ->whereMonth('work_day', date('m'))
                        ->whereYear('work_day', date('Y'))
                )
        ];
    }
}
