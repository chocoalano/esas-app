<?php

namespace App\Http\Controllers;

use App\Models\AdministrationApp\QrPresence;
use App\Models\AdministrationApp\QrPresenceTransaction;
use App\Models\AdministrationApp\UserAttendance;
use App\Models\CoreApp\Company;
use App\Models\CoreApp\Departement;
use App\Models\CoreApp\TimeWork;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeController extends Controller
{
    public function index(Request $request)
    {
        // Mendapatkan waktu saat ini
        $currentTime = Carbon::now();
        // Menambahkan waktu kedaluwarsa (30 detik dari waktu saat ini)
        $expiresAt = $currentTime->copy()->addSeconds(30);
        // Membuat token yang aman
        $token = Crypt::encryptString($currentTime->format('Y-m-d H:i:s'));

        // Validasi input
        $validated = $request->validate([
            'type_presence' => 'nullable|string|in:in,out',
            'departement_selected' => 'nullable|numeric|exists:departements,id',
            'timework_selected' => 'nullable|numeric|exists:time_workes,id',
        ]);

        // Proses penyimpanan data QR jika type_presence ada
        if (!empty($validated['type_presence']) && !empty($validated['departement_selected']) && !empty($validated['timework_selected'])) {
            $qr = QrPresence::firstOrCreate(
                ['token' => $token], // Pastikan token unik
                [
                    'type' => $validated['type_presence'],
                    'departement_id' => $validated['departement_selected'],
                    'timework_id' => $validated['timework_selected'],
                    'for_presence' => $currentTime,
                    'expires_at' => $expiresAt,
                ]
            );
            // Membuat QR code dengan data yang dinamis
            $qrCode = QrCode::size(200)->generate(json_encode($qr));
        } else {
            // Jika type_presence tidak ada, set qrCode sebagai null
            $qrCode = null;
        }

        // Mengambil data departemen
        $departement = Departement::all();

        // Jika departement_selected diberikan, ambil data TimeWork berdasarkan departemen tersebut
        $timework = null; // Set default value
        if (!empty($validated['departement_selected'])) {
            $timework = TimeWork::whereHas('department', function ($query) use ($validated) {
                $query->where('id', $validated['departement_selected']);
            })->get();
        }

        // Mengembalikan view dengan QR code, departemen, dan timework
        return view('pages.welcome', compact('qrCode', 'departement', 'timework'));
    }
    public function face_recognition(Request $request)
    {
        $validated = $request->validate([
            'type_presence' => 'nullable|string|in:in,out',
            'departement_selected' => 'nullable|numeric|exists:departements,id',
            'timework_selected' => 'nullable|numeric|exists:time_workes,id',
            'nip' => 'nullable|numeric|exists:users,nip',
        ]);
        $departement = Departement::all();

        // Jika departement_selected diberikan, ambil data TimeWork berdasarkan departemen tersebut
        $timework = null; // Set default value
        if (!empty($validated['departement_selected'])) {
            $timework = TimeWork::whereHas('department', function ($query) use ($validated) {
                $query->where('id', $validated['departement_selected']);
            })->get();
        }
        return view('pages.facerecognition', compact('departement', 'timework'));
    }
    public function face_recognition_store(Request $request)
    {
        $validated = $request->validate([
            'nip' => 'required|numeric|exists:users,nip',
            'departement' => 'required|numeric|exists:departements,id',
            'timework' => 'required|numeric|exists:time_workes,id',
            'type' => 'required|string|in:in,out',
        ]);

        $user = User::with('company')->where('nip', $validated['nip'])->firstOrFail();
        $time = now()->format('H:i:s');

        if ($validated['type'] === 'out' && !UserAttendance::where('user_id', $user->id)->whereDate('created_at', now())->exists()) {
            return $this->sendError('Terjadi kesalahan.', ['error' => 'Anda harus absen masuk terlebih dulu sebelum absen pulang!'], 404);
        }

        // Tentukan stored procedure yang akan digunakan
        $procedure = $validated['type'] === 'in' ? 'UpdateAttendanceIn' : 'UpdateAttendanceOut';

        // Jalankan stored procedure
        try {
            $exec = DB::select("CALL {$procedure}(?,?,?,?,?,?)", [
                $user->id,
                $validated['timework'],
                $user->company->latitude,
                $user->company->longitude,
                "{$user->nip}.png",
                $time
            ]);

            // Cek status eksekusi
            $status = !empty($exec) && $exec[0]->success === 1;

            return $status
                ? $this->sendResponse('success', "Absensi {$validated['type']} berhasil disimpan.")
                : $this->sendError('Terjadi kesalahan.', ['error' => 'Gagal menyimpan absensi.'], 500);
        } catch (QueryException $e) {
            // Tangkap exception database
            $errorCode = $e->getCode();
            $errorMessage = $e->getMessage();

            // Log error untuk debugging
            \Log::error("Error Database (QueryException): " . $errorMessage);

            // Berikan respons error yang lebih spesifik (opsional)
            if ($errorCode === '45000') { // Contoh: Error dari SIGNAL di stored procedure
                // Ekstrak pesan dari SQLSTATE
                $message = substr($errorMessage, strpos($errorMessage, "SQLSTATE[45000]:") + 16);
                return response()->json(['status' => 'error', 'message' => $message], 400); // Bad Request
            }

            return $this->sendError('Terjadi kesalahan database.', $e->getMessage(), 500); // Internal Server Error
        } catch (\Exception $e) {
            // Tangkap exception umum
            $errorMessage = $e->getMessage();

            // Log error untuk debugging
            \Log::error("Error Umum: " . $errorMessage);

            return $this->sendError('Terjadi kesalahan server.', $e->getMessage(), 500 ); // Internal Server Error
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'type' => 'required|string|in:in,out',
            'token' => 'required|string|exists:qr_presences,token',
        ]);

        try {
            return $this->qrAttendance(Auth::id(), $validatedData['type'], $validatedData['token']);
        } catch (\Exception $e) {
            // dd($e->getMessage());
            return $this->sendError('Terjadi kesalahan server.', $e->getMessage(), 500 );
        }
    }

    private function qrAttendance(int $userId, string $type, string $token)
    {
        if (!in_array($type, ['in', 'out'])) {
            throw new \Exception('Tipe absen tidak valid!');
        }

        $currentTime = Carbon::now();
        $currentDate = Carbon::today();

        // Ambil data QR Presence
        $qrPresence = QrPresence::with(['departement', 'timeWork'])
            ->where('token', $token)
            ->first();

        if (!$qrPresence) {
            throw new \Exception('Token tidak ditemukan!');
        }

        // Cek apakah QR sudah digunakan
        if (QrPresenceTransaction::where('qr_presence_id', $qrPresence->id)->exists()) {
            throw new \Exception('Kode QR sudah digunakan!');
        }

        // Cek apakah QR sudah expired
        if ($currentTime->gt($qrPresence->expires_at)) {
            throw new \Exception('Kode QR sudah kadaluarsa!');
        }

        // Cek apakah user terdaftar di departemen QR
        if (
            !User::where('id', $userId)->whereHas('employee', function ($query) use ($qrPresence) {
                $query->where('departement_id', $qrPresence->departement_id);
            })->exists()
        ) {
            throw new \Exception('User tidak terdaftar di departemen ini.');
        }

        // Ambil jadwal kerja user
        $scheduleId = DB::table('user_timework_schedules')
            ->where('user_id', $userId)
            ->where('work_day', $currentDate)
            ->value('id');

        // Jika absen keluar, pastikan sudah ada absen masuk
        if ($type === 'out' && !UserAttendance::where('user_id', $userId)->whereDate('created_at', $currentDate)->whereNotNull('time_in')->exists()) {
            throw new \Exception('Anda harus melakukan absensi masuk sebelum absensi pulang!');
        }

        $status = $currentTime->lt($qrPresence->timeWork->in) ? 'normal' : 'late';
        $statusInOut = ($type === 'in') ? $status : ($currentTime->lt($qrPresence->timeWork->in) ? 'unlate' : 'normal');

        $company = Company::select('latitude', 'longitude')->find($qrPresence->timeWork->company_id);

        DB::beginTransaction();

        $attendance = UserAttendance::where('user_id', $userId)->whereDate('created_at', $currentDate)->first();

        if ($attendance) {
            $attendance->update([
                'updated_at' => $currentTime,
                'time_in' => $type === 'in' ? $currentTime : $attendance->time_in,
                'status_in' => $type === 'in' ? $statusInOut : $attendance->status_in,
                'lat_in' => $type === 'in' ? $company->latitude : $attendance->lat_in,
                'long_in' => $type === 'in' ? $company->longitude : $attendance->long_in,
                'time_out' => $type === 'out' ? $currentTime : $attendance->time_out,
                'status_out' => $type === 'out' ? $statusInOut : ($attendance->status_out ?? 'normal'),
                'lat_out' => $type === 'out' ? $company->latitude : $attendance->lat_out,
                'long_out' => $type === 'out' ? $company->longitude : $attendance->long_out,
            ]);
        } else {
            $attendance = UserAttendance::create([
                'user_id' => $userId,
                'user_timework_schedule_id' => $scheduleId,
                'created_at' => $currentTime,
                'updated_at' => $currentTime,
                'time_in' => $type === 'in' ? $currentTime : null,
                'status_in' => $type === 'in' ? $statusInOut : 'normal',
                'lat_in' => $type === 'in' ? $company->latitude : null,
                'long_in' => $type === 'in' ? $company->longitude : null,
                'time_out' => $type === 'out' ? $currentTime : null,
                'status_out' => $type === 'out' ? $statusInOut : 'normal',
                'lat_out' => $type === 'out' ? $company->latitude : null,
                'long_out' => $type === 'out' ? $company->longitude : null,
            ]);
        }

        QrPresenceTransaction::create([
            'qr_presence_id' => $qrPresence->id,
            'user_attendance_id' => $attendance->id,
            'token' => $token,
            'created_at' => $currentTime,
            'updated_at' => $currentTime,
        ]);

        DB::commit();

        return response()->json([
            'message' => 'success',
            'result' => "Absensi {$type} berhasil disimpan."
        ]);
    }

}
