<?php

namespace Database\Seeders;

use App\Models\UserApp\UserDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\CoreApp\Company;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserSeeder extends Seeder
{
    /**
     * Cari kecocokan string berdasarkan persentase kemiripan.
     */
    private function cariKecocokan(array $data, string $str): ?string
    {
        $normalizedInput = strtolower($str);

        return collect($data)
            ->filter(function ($value) use ($normalizedInput) {
                similar_text($normalizedInput, strtolower($value), $percent);
                return $percent > 50; // Ambang batas kemiripan lebih dari 50%
            })
            ->keys()
            ->first();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::first();
        $users = DB::connection('mysql_esas_live')
            ->table('users as u')
            ->select(
                'u.*',
                'ua.idtype',
                'ua.idnumber',
                'ua.idexpired',
                'ua.postalcode',
                'ua.citizen_id_address',
                'ua.residential_address',
                'ub.bank_name',
                'ub.bank_account',
                'ub.bank_account_holder',
                'ue.approval_line',
                'ue.approval_manager',
                'ue.join_date',
                'ue.sign_date',
                'o.name as departement',
                'jl.name as levels',
                'jp.name as position',
                'us.basic_salary',
                'us.salary_type',
                'us.payment_schedule',
                'us.prorate_settings',
                'us.overtime_settings',
                'us.cost_center',
                'us.cost_center_category',
                'us.currency'
            )
            ->join('u_addres as ua', 'u.id', '=', 'ua.user_id')
            ->join('u_banks as ub', 'u.id', '=', 'ub.user_id')
            ->join('u_employes as ue', 'u.id', '=', 'ue.user_id')
            ->join('organizations as o', 'ue.organization_id', '=', 'o.id')
            ->join('job_levels as jl', 'ue.job_level_id', '=', 'jl.id')
            ->join('job_positions as jp', 'ue.job_position_id', '=', 'jp.id')
            ->join('u_salaries as us', 'u.id', '=', 'us.user_id')
            ->get();

        foreach ($users as $userData) {
            // Cari data departemen, level, dan posisi
            $dept = DB::table('departements as d')
                ->join('job_levels as jl', 'd.id', '=', 'jl.departement_id')
                ->join('job_positions as jp', 'd.id', '=', 'jp.departement_id')
                ->select('d.id as departement_id', 'jl.id as level_id', 'jp.id as position_id')
                ->where('d.company_id', $company?->id)
                ->where('d.name', $userData->departement)
                ->where('jl.name', $userData->levels)
                ->where('jp.name', $userData->position)
                ->first();

            // Validasi data departemen
            if (!$dept) {
                // Log data error
                Log::warning('Invalid department, position, or level data.', [
                    'departement' => $userData->departement,
                    'levels' => $userData->levels,
                    'position' => $userData->position,
                ]);

                // Gunakan nilai default
                $dept = (object) [
                    'departement_id' => 3,
                    'level_id' => 17,
                    'position_id' => 104,
                ];
            }

            $params = [
                'nip' => $userData->nik,
                'name' => $userData->name,
                'email' => $userData->email,
                'password' => Hash::make($userData->nik),
                'company_id' => $company?->id,
                'idtype' => $userData->idtype,
                'citizen_id_address' => $userData->citizen_id_address,
                'residential_address' => $userData->residential_address,
                'phone' => $userData->phone,
                'placebirth' => $userData->placebirth,
                'datebirth' => $userData->datebirth,
                'gender' => $this->cariKecocokan(UserDetail::GENDER, $userData->gender) ?? 'm',
                'blood' => $this->cariKecocokan(UserDetail::BLOOD_TYPE, $userData->blood),
                'marital_status' => $this->cariKecocokan(UserDetail::MARITAL_STATUS, $userData->marital_status),
                'religion' => $this->cariKecocokan(UserDetail::RELIGION, $userData->religion),
                'basic_salary' => (float) $userData->basic_salary ?? 0.00,
                'salary_type' => $userData->salary_type ?? 'Monthly',
                'departement' => $dept->departement_id,
                'levels' => $dept->level_id,
                'position' => $dept->position_id,
                'approval_line' => $userData->approval_line ?? 1,
                'approval_manager' => $userData->approval_manager ?? 1,
                'join_date' => $userData->join_date ?? now()->format('Y-m-d'),
                'sign_date' => $userData->sign_date ?? now()->format('Y-m-d'),
                'bank_name' => $userData->bank_name ?? 'BCA',
                'bank_account' => $userData->bank_account ?? '0',
                'bank_account_holder' => $userData->bank_account_holder ?? $userData->name,
            ];

            try {
                DB::statement(
                    "CALL StoreOrUpdateUser(
                            :nip, :name, :email, :password, :company_id,
                            :idtype, :citizen_id_address, :residential_address, :phone,
                            :placebirth, :datebirth, :gender, :blood, :marital_status,
                            :religion, :basic_salary, :salary_type, :departement,
                            :levels, :position, :approval_line, :approval_manager,
                            :join_date, :sign_date, :bank_name, :bank_account,
                            :bank_account_holder
                        )",
                    $params
                );
            } catch (\Exception $e) {
                Log::error('Failed to execute stored procedure.', [
                    'params' => $params,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
