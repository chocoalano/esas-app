<?php
namespace App\Repositories\Services\AdministrationApp;

use App\Models\AdministrationApp\UserAttendance;
use App\Models\AdministrationApp\UserTimeworkSchedule;
use App\Models\CoreApp\TimeWork;
use App\Models\views\AttendanceView;
use App\Repositories\Interfaces\AdministrationApp\AttendanceInterface;
use App\Support\StringSupport;
use App\Support\UploadFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceService implements AttendanceInterface
{
    protected $model;

    public function __construct(UserAttendance $model)
    {
        $this->model = $model;
    }

    /**
     * @inheritDoc
     */
    public function all()
    {
        return $this->model->all();
    }

    /**
     * @inheritDoc
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $find = $this->model->find($id);
        if ($find) {
            $find->delete();
        }
        return $find;
    }

    /**
     * @inheritDoc
     */
    public function find(int $id)
    {
        return $this->model->find($id);
    }
    public function findbySchedule(int $id)
    {
        return $this->model->whereHas('schedule', function ($q) use ($id) {
            $q->where('id', $id);
        })->first();
    }

    /**
     * @inheritDoc
     */
    public function paginate(int $page, int $limit, ?string $search = null)
    {
        $auth_user = Auth::user();
        $query = AttendanceView::query();
        $query->where([
            'user_id' => $auth_user->id,
            'company_id' => $auth_user->company_id,
        ]);
        if (!empty($search)) {
            $query
                ->whereMonth('created_at', $search)
                ->whereYear('created_at', Carbon::now()->year);
        }

        return $query->orderByDesc('created_at')
            ->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data)
    {
        $find = $this->model->findOrFail($id);
        if ($find) {
            $find->update($data);
        }
        return $find;
    }

    /**
     * @inheritDoc
     */
    public function presence_in(array $data)
    {
        $requiredFields = ['time_id', 'lat', 'long', 'type', 'image', 'time'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return false;
            }
        }
        $user = Auth::user();
        $attendance = $this->model->where([
            'user_id' => $user->id,
            'time_in' => $data['time'],
        ])
            ->whereDate('created_at', Carbon::now()->format('Y-m-d'))
            ->first();
        if ($attendance) {
            UploadFile::unlink($attendance->image_in);
        }
        $upload = UploadFile::uploadWithResize($data['image'], 'attendance-in');
        $schedule = UserTimeworkSchedule::where([
            'user_id' => $user->id,
            'work_day' => Carbon::now()->format('Y-m-d'),
            'time_work_id' => $data['time_id']
        ])->first();
        $cekjam = TimeWork::find($data['time_id']);
        $currentTime = Carbon::createFromFormat('H:i:s', $cekjam->in);
        $inTime = Carbon::createFromFormat('H:i:s', $data['time']);
        $status = $currentTime->greaterThan($inTime) ? 'normal' : 'late';

        if (!$attendance) {
            $attendance = new $this->model();
        }

        $attendance->user_id = $user->id;
        $attendance->user_timework_schedule_id = $schedule?->id;
        $attendance->time_in = $data['time'];
        $attendance->lat_in = $data['lat'];
        $attendance->long_in = $data['long'];
        $attendance->image_in = $upload;
        $attendance->status_in = $status;
        $attendance->save();

        return true;
    }

    /**
     * @inheritDoc
     */
    public function presence_out(array $data)
    {
        $requiredFields = ['time_id', 'lat', 'long', 'type', 'image', 'time'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return false;
            }
        }
        $user = Auth::user();
        $attendance = $this->model->where('user_id', $user->id)
            ->whereNotNull('time_in')
            ->whereDate('created_at', Carbon::now()->format('Y-m-d'))
            ->first();
        UploadFile::unlink($attendance->image_out);
        $upload = UploadFile::uploadWithResize($data['image'], 'attendance-out');
        $schedule = UserTimeworkSchedule::where([
            'user_id' => $user->id,
            'work_day' => Carbon::now()->format('Y-m-d'),
            'time_work_id' => $data['time_id']
        ])->first();
        $cekjam = TimeWork::find($data['time_id']);
        $currentTime = Carbon::createFromFormat('H:i:s', $cekjam->out);
        $outTime = Carbon::createFromFormat('H:i:s', $data['time']);
        $status = $currentTime->greaterThan($outTime) ? 'unlate' : 'normal';

        if (!$attendance) {
            return false;
        }

        $attendance->user_id = $user->id;
        $attendance->user_timework_schedule_id = $schedule?->id;
        $attendance->time_out = $data['time'];
        $attendance->lat_out = $data['lat'];
        $attendance->long_out = $data['long'];
        $attendance->image_out = $upload;
        $attendance->status_out = $status;
        $attendance->save();

        return true;
    }
    /**
     * @inheritDoc
     */
    public function countAll()
    {
        return $this->model->count();
    }
    /**
     * @inheritDoc
     */
    public function chart(string $filter)
    {
        $startDate = null;
        $endDate = null;
        $labels = [];

        // Determine the date range and labels based on the filter
        switch ($filter) {
            case 'today':
                $startDate = Carbon::today();
                $endDate = Carbon::today();
                $labels = [$startDate->format('Y-m-d')];
                break;
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                $labels = StringSupport::generateDateLabels($startDate, $endDate);
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                $labels = StringSupport::generateDateLabels($startDate, $endDate);
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                $labels = [
                    'Jan',
                    'Feb',
                    'Mar',
                    'Apr',
                    'May',
                    'Jun',
                    'Jul',
                    'Aug',
                    'Sep',
                    'Oct',
                    'Nov',
                    'Dec'
                ];
                break;
            default:
                return ['labels' => ['No Data'], 'late' => [], 'unlate' => [], 'normal' => []];
        }

        // Ensure valid date range is set
        if (!$startDate || !$endDate) {
            return ['labels' => $labels, 'late' => [], 'unlate' => [], 'normal' => []];
        }

        // Fetch grouped data from the database
        $groupedData = DB::table('user_attendances as ua')
            ->selectRaw('COUNT(*) as total, DATE(uts.work_day) as date, ua.status_in')
            ->join('user_timework_schedules as uts', 'uts.id', '=', 'ua.user_timework_schedule_id')
            ->whereBetween('uts.work_day', [$startDate, $endDate])
            ->groupByRaw('DATE(uts.work_day), ua.status_in')
            ->get()
            ->groupBy('status_in');

        // Initialize datasets
        $late = array_fill(0, count($labels), 0);
        $unlate = array_fill(0, count($labels), 0);
        $normal = array_fill(0, count($labels), 0);

        // Map grouped data to the respective dataset
        foreach ($groupedData as $status => $data) {
            foreach ($data as $entry) {
                $labelIndex = array_search($entry->date, $labels);
                if ($labelIndex !== false) {
                    switch ($status) {
                        case 'late':
                            $late[$labelIndex] = $entry->total;
                            break;
                        case 'unlate':
                            $unlate[$labelIndex] = $entry->total;
                            break;
                        case 'normal':
                            $normal[$labelIndex] = $entry->total;
                            break;
                    }
                }
            }
        }

        return [
            'labels' => $labels,
            'late' => $late,
            'unlate' => $unlate,
            'normal' => $normal,
        ];
    }
}
