<?php
namespace App\Repositories\Services\AdministrationApp;

use App\Jobs\InsertUpdateScheduleJob;
use App\Models\AdministrationApp\UserTimeworkSchedule;
use App\Repositories\Interfaces\AdministrationApp\ScheduleAttendanceInterface;
use App\Repositories\Interfaces\CoreApp\DepartementInterface;
use App\Repositories\Interfaces\CoreApp\TimeWorkInterface;
use App\Repositories\Interfaces\CoreApp\UserInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ScheduleAttendanceService implements ScheduleAttendanceInterface
{
    protected $model;

    public function __construct(UserTimeworkSchedule $model)
    {
        $this->model = $model;
    }

    /**
     * @inheritDoc
     */
    public function import(array $data)
    {
        $jadwal = [];
        // Gunakan DB Transaction untuk memastikan semua proses berhasil atau dibatalkan
        foreach ($data as $k) {
            if (
                isset($k['company'], $k['nip'], $k['name'], $k['department'], $k['shift_name'], $k['shift_date']) ||
                !empty($k['company']) || !empty($k['nip']) || !empty($k['name']) ||
                !empty($k['department']) || !empty($k['shift_name']) || !empty($k['shift_date'])
                ) {
                // Temukan user berdasarkan NIP
                $user = DB::table('users')
                    ->join('user_employes', 'users.id', '=', 'user_employes.user_id')
                    ->join('companies', 'users.company_id', '=', 'companies.id')
                    ->where('users.nip', $k['nip'])
                    ->where('companies.name', $k['company'])
                    ->select('users.company_id', 'users.id as user_id', 'user_employes.departement_id')
                    ->first();
                if (!$user || !$user->company_id || !$user->departement_id) {
                    // Jika user tidak ditemukan, lewati data ini
                    continue;
                }
                // Temukan TimeWork berdasarkan nama shift dan department ID
                $time = DB::table('departements')
                    ->join('time_workes', 'departements.id', '=', 'time_workes.departemen_id')
                    ->join('companies', 'time_workes.company_id', '=', 'companies.id')
                    ->where('departements.id', $user->departement_id)
                    ->where('companies.id', $user->company_id)
                    ->where('time_workes.name', 'LIKE', '%' . $k['shift_name'] . '%')
                    ->select('departements.*', 'time_workes.*', 'companies.*')
                    ->first();
                if (!$time) {
                    // Jika TimeWork tidak ditemukan, lewati data ini
                    continue;
                }
                // Tambahkan jadwal untuk user
                $jadwal[] = [
                    'user_id' => $user->user_id,
                    'time_work_id' => $time->id,
                    'work_day' => $k['shift_date'],
                ];
            }
            continue;
        }
        // Masukkan jadwal ke dalam database
        // Dispatch job jika diperlukan
        if (!empty($jadwal)) {
            InsertUpdateScheduleJob::dispatch($jadwal);
        }
        return true;
    }
    /**
     * @inheritDoc
     */
    public function template()
    {
        $spreadsheet = new Spreadsheet();

        // Sheet utama
        $mainSheet = $spreadsheet->getActiveSheet();
        $mainSheet->setTitle('Main Data');

        // Header untuk sheet utama
        $headers = [
            'A' => 'company',
            'B' => 'nip',        // Akan menjadi select option
            'C' => 'name',
            'D' => 'department',
            'E' => 'shift_name', // Akan menjadi select option
            'F' => 'shift_date',
        ];

        // Loop untuk mengisi header
        foreach ($headers as $column => $header) {
            $mainSheet->setCellValue("{$column}1", $header);
        }

        // Ambil data untuk bulan ini
        $currentMonthStart = Carbon::now()->startOfMonth()->toDateString();
        $currentMonthEnd = Carbon::now()->endOfMonth()->toDateString();

        $data_template = DB::table('users as u')
            ->join('companies as c', 'c.id', '=', 'u.company_id')
            ->join('user_employes as ue', 'ue.user_id', '=', 'u.id')
            ->join('departements as d', 'd.id', '=', 'ue.departement_id')
            ->join('user_timework_schedules as uts', 'uts.user_id', '=', 'u.id')
            ->join('time_workes as tw', 'uts.time_work_id', '=', 'tw.id');

        // Filter data berdasarkan role pengguna
        if (Auth::user()->hasRole(['Admin Departement', 'Member'])) {
            $data_template->where('c.name', Auth::user()->company->name)
                ->where('d.id', Auth::user()->employee->departement_id);
        }

        // Select kolom yang diinginkan
        $data_template->select([
            'c.name as company_name',
            'u.nip as user_nip',
            'u.name as user_name',
            'd.name as department_name',
            'tw.name as shift_name',
            'uts.work_day as schedule_date',
        ]);

        // Filter berdasarkan rentang tanggal
        $data_template->whereBetween('uts.work_day', [$currentMonthStart, $currentMonthEnd]);

        // Eksekusi query dan dapatkan hasil
        $results = $data_template->get();

        // Cek jika data_template tidak kosong
        if (!$results->isEmpty()) {
            // Tulis data ke sheet utama mulai dari baris kedua
            $i = 2;
            foreach ($results as $key) {
                $mainSheet->setCellValue("A{$i}", $key->company_name);
                $mainSheet->setCellValue("B{$i}", $key->user_nip);
                $mainSheet->setCellValue("C{$i}", $key->user_name);
                $mainSheet->setCellValue("D{$i}", $key->department_name);
                $mainSheet->setCellValue("E{$i}", $key->shift_name);
                $mainSheet->setCellValue("F{$i}", $key->schedule_date);
                $i++;
            }
        }

        // Sheet baru untuk Shift Options
        $shiftSheet = $spreadsheet->createSheet();
        $shiftSheet->setTitle('Shift Options');

        // Ambil data shift untuk referensi
        $shifts = DB::table('time_workes')
            ->where([
                'company_id' => Auth::user()->company_id,
                'departemen_id' => Auth::user()->employee->departement_id,
            ])
            ->select('name')->get();

        // Isi data Shift Options
        $shiftSheet->setCellValue('A1', 'Shift Name');

        if (!$shifts->isEmpty()) {
            $j = 2;
            foreach ($shifts as $shift) {
                $shiftSheet->setCellValue("A{$j}", $shift->name);
                $j++;
            }

            // Definisikan Named Range untuk rentang opsi jika data shift ada
            $spreadsheet->addNamedRange(
                new \PhpOffice\PhpSpreadsheet\NamedRange(
                    'ShiftOptions',
                    $shiftSheet,
                    "\$A\$2:\$A\$" . ($j - 1)
                )
            );
        }

        // Sheet baru untuk Users
        $userSheet = $spreadsheet->createSheet();
        $userSheet->setTitle('Users');

        // Ambil data user untuk referensi
        $users = DB::table('users')
            ->select(['nip', 'name'])
            ->join('user_employes', 'user_employes.user_id', '=', 'users.id')
            ->where('users.company_id', Auth::user()->company_id)
            ->where('user_employes.departement_id', Auth::user()->employee->departement_id)
            ->get();

        // Isi data Users
        $userSheet->setCellValue('A1', 'NIP');
        $userSheet->setCellValue('B1', 'Name');

        if (!$users->isEmpty()) {
            $k = 2;
            foreach ($users as $user) {
                $userSheet->setCellValue("A{$k}", $user->nip);
                $userSheet->setCellValue("B{$k}", $user->name);
                $k++;
            }

            // Definisikan Named Range untuk rentang opsi NIP
            $spreadsheet->addNamedRange(
                new \PhpOffice\PhpSpreadsheet\NamedRange(
                    'NIPOptions',
                    $userSheet,
                    "\$A\$2:\$A\$" . ($k - 1)
                )
            );
        }

        // Set dropdown pada kolom NIP di sheet utama
        $highestRow = $mainSheet->getHighestRow();
        $nipDropdownRange = 'NIPOptions';

        for ($row = 2; $row <= $highestRow; $row++) {
            $validation = $mainSheet->getCell("B{$row}")->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setFormula1($nipDropdownRange);
        }

        // Nama file untuk di-download
        $fileName = 'template-import-schedule-attendance-' . Carbon::now()->format('YmdHis') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $filePath = public_path($fileName);

        // Simpan file ke public_path dan kembalikan response untuk download
        $writer->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend();
    }
    /**
     * @inheritDoc
     */
    public function time_validation(int $scheduleId, int $userId, string $timeInOrOut, string $currentTime): string
    {
        $find = $this->model->with('timework')
            ->where('id', $scheduleId)
            ->where('user_id', $userId)
            ->first();
        if (!$find || !$find->timework) {
            throw new \Exception("Schedule or Timework not found.");
        }
        $timework = $find->timework;
        return match ($timeInOrOut) {
            'in' => $timework->in > $currentTime ? 'unlate' : 'late',
            'out' => $timework->out > $currentTime ? 'late' : 'normal',
            default => throw new \InvalidArgumentException("Invalid timeInOrOut value: $timeInOrOut"),
        };
    }
    /**
     * @inheritDoc
     */
    public function find(int $id)
    {
        return $this->model->with(
            'user',
            'timework',
            'employee',
            'company'
        )
            ->find($id);
    }
    /**
     * @inheritDoc
     */
    public function update(int $id, array $data)
    {
        $find = $this->model->find($id);
        $find->update($data);
        return $find;
    }
}
