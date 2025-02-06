<?php

namespace Database\Seeders;

use App\Models\CoreApp\Departement;
use App\Models\CoreApp\JobLevel;
use App\Models\CoreApp\JobPosition;
use App\Models\CoreApp\TimeWork;
use App\Models\User;
use App\Models\CoreApp\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class ComDeptLvlPosition extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            Role::insert([
                [
                    'name' => 'Administrator',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'Admin Departement',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'Member',
                    'guard_name' => 'web',
                ]
            ]);
            // Buat perusahaan baru
            $company = Company::create([
                'name' => 'PT. SINERGI ABADI SENTOSA',
                'latitude' => '-6.17566156928234',
                'longitude' => '106.599255891093',
                'radius' => 30,
                'full_address' => 'Jl. Prabu Kian Santang No.169A, RT.001/RW.004, Sangiang Jaya, Kec. Periuk, Kota Tangerang, Banten 15132',
            ]);

            // Buat pengguna admin
            $user = User::create([
                'company_id' => $company->id,
                'nip' => '0000',
                'name' => 'Admin',
                'email' => 'admin@sas.tes',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('123456789'),
                'avatar' => 'admin.jpg',
                'status' => 'active',
            ]);

            // Tetapkan role super admin
            $user->assignRole('super_admin');

            // Ambil data dari database eksternal
            $organizations = DB::connection('mysql_esas_live')->table('organizations')->get();
            $jobLevels = DB::connection('mysql_esas_live')->table('job_levels')->get();
            $jobPositions = DB::connection('mysql_esas_live')->table('job_positions')->get();

            // Validasi jika data kosong
            if ($organizations->isEmpty() || $jobLevels->isEmpty() || $jobPositions->isEmpty()) {
                $this->command->warn('Data organisasi, job levels, atau job positions kosong. Seeder akan dilewati.');
                return;
            }

            // Proses organisasi dan relasi terkait
            foreach ($organizations as $organization) {
                $department = Departement::updateOrCreate(
                    ['name' => $organization->name],
                    ['company_id' => $company->id, 'name' => $organization->name]
                );

                $nonshift=[
                    'company_id' => $company->id,
                    'departemen_id'=> $department->id,
                    'name' => 'Nonshift',
                    'in' => '09:00:00',
                    'out' => '18:00:00',
                ];
                $shift_satu=[
                    'company_id' => $company->id,
                    'departemen_id'=> $department->id,
                    'name' => 'Shift 1',
                    'in' => '07:00:00',
                    'out' => '15:00:00',
                ];
                $shift_dua=[
                    'company_id' => $company->id,
                    'departemen_id'=> $department->id,
                    'name' => 'Shift 2',
                    'in' => '15:00:00',
                    'out' => '22:00:00',
                ];
                if (in_array($organization->name, ['PRODUKSI', 'MAINTENANCE', 'QUALITY CONTROL', 'HRGA', 'WAREHOUSE'])) {
                    $merge = array_merge([$nonshift], [$shift_satu], [$shift_dua]);
                    TimeWork::insert($merge);
                }else{
                    TimeWork::create($nonshift);
                }

                foreach ($jobLevels as $jobLevel) {
                    JobLevel::updateOrCreate(
                        [
                            'company_id' => $company->id,
                            'departement_id' => $department->id,
                            'name' => $jobLevel->name,
                        ],
                        []
                    );
                }

                foreach ($jobPositions as $jobPosition) {
                    JobPosition::updateOrCreate(
                        [
                            'company_id' => $company->id,
                            'departement_id' => $department->id,
                            'name' => $jobPosition->name,
                        ],
                        []
                    );
                }
            }
        });
    }
}
