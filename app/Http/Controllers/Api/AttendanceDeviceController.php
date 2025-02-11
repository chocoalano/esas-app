<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdministrationApp\QrPresence;
use App\Models\AdministrationApp\UserAttendance;
use App\Models\CoreApp\Departement;
use App\Models\User;
use App\Repositories\Interfaces\CoreApp\DepartementInterface;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AttendanceDeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function company(Request $request, DepartementInterface $proses)
    {
        try {
            $input = $request->all();
            $data = $proses->companyall($input['search']);
            return $this->sendResponse($data, 'List show shift successfully.');
        } catch (\Exception $e) {
            return $this->sendError('List failed.', ['error' => $e->getMessage()], 500);
        }
    }
    public function departement(Request $request, DepartementInterface $proses)
    {
        try {
            $input = $request->all();
            $data = $proses->all($input['companyId'], $input['search']);
            return $this->sendResponse($data, 'List show departement successfully.');
        } catch (\Exception $e) {
            return $this->sendError('List failed.', ['error' => $e->getMessage()], 500);
        }
    }
    public function shift(Request $request, DepartementInterface $proses)
    {
        try {
            $input = $request->all();
            $data = $proses->shift($input['companyId'], $input['deptId']);
            return $this->sendResponse($data, 'List show shift successfully.');
        } catch (\Exception $e) {
            return $this->sendError('List failed.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'company_id' => 'required|exists:companies,id', // company_id harus ada dan ada di tabel companies
                'departement_id' => 'required|exists:departements,id', // departement_id harus ada dan ada di tabel departements
                'shift_id' => 'required|exists:time_workes,id', // shift_id harus ada dan ada di tabel shifts
                'type_presence' => 'required|in:in,out', // type_presence harus 'in' atau 'out'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422); // Kembalikan error jika validasi gagal
            }
            $input = $validator->getData();
            // Mendapatkan waktu saat ini
            $currentTime = Carbon::now();
            // Menambahkan waktu kedaluwarsa (30 detik dari waktu saat ini)
            $expiresAt = $currentTime->copy()->addSeconds(30);
            // Membuat token yang aman
            $token = Crypt::encryptString($currentTime->format('Y-m-d H:i:s'));
            $qr = QrPresence::firstOrCreate(
                ['token' => $token], // Pastikan token unik
                [
                    'type' => $input['type_presence'],
                    'departement_id' => $input['departement_id'],
                    'timework_id' => $input['shift_id'],
                    'for_presence' => $currentTime,
                    'expires_at' => $expiresAt,
                ]
            );
            return $this->sendResponse($qr, 'Save token qr successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Save token qr failed.', ['error' => $e->getMessage()], 500);
        }
    }
    public function face_attendance(Request $request)
    {
        $validated = $request->validate([
            'nip' => 'required|numeric|exists:users,nip',
            'departement_id' => 'required|numeric|exists:departements,id',
            'shift_id' => 'required|numeric|exists:time_workes,id',
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
                $validated['shift_id'],
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
            dd($e);
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
            dd($e);
            // Tangkap exception umum
            $errorMessage = $e->getMessage();

            // Log error untuk debugging
            \Log::error("Error Umum: " . $errorMessage);

            return $this->sendError('Terjadi kesalahan server.', $e->getMessage(), 500 ); // Internal Server Error
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
