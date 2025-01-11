<?php

namespace Database\Seeders;

use App\Models\AdministrationApp\PermitType;
use Illuminate\Database\Seeder;

class PermitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $type = [
            [
                'type' => 'cuti tahunan',
                'is_payed' => true,
                'approve_line' => true,
                'approve_manager' => true,
                'approve_hr' => true,
            ],
            [
                'type' => 'cuti menikah',
                'is_payed' => true,
                'approve_line' => true,
                'approve_manager' => true,
                'approve_hr' => true,
            ],
            [
                'type' => 'cuti menikahkan anak',
                'is_payed' => true,
                'approve_line' => true,
                'approve_manager' => true,
                'approve_hr' => true,
            ],
            [
                'type' => 'cuti khitan',
                'is_payed' => true,
                'approve_line' => true,
                'approve_manager' => true,
                'approve_hr' => true,
            ],
            [
                'type' => 'cuti khitanan anak',
                'is_payed' => true,
                'approve_line' => true,
                'approve_manager' => true,
                'approve_hr' => true,
            ],
            [
                'type' => 'cuti baptis',
                'is_payed' => true,
                'approve_line' => true,
                'approve_manager' => true,
                'approve_hr' => true,
            ],
            [
                'type' => 'cuti baptis anak',
                'is_payed' => true,
                'approve_line' => true,
                'approve_manager' => true,
                'approve_hr' => true,
            ],
            [
                'type' => 'cuti istri melahirkan/keguguran',
                'is_payed' => true,
                'approve_line' => true,
                'approve_manager' => true,
                'approve_hr' => true,
            ],
            [
                'type' => 'cuti keluarga meninggal',
                'is_payed' => true,
                'approve_line' => true,
                'approve_manager' => true,
                'approve_hr' => true,
            ],
            [
                'type' => 'cuti anggota keluarga serumah meninggal',
                'is_payed' => true,
                'approve_line' => true,
                'approve_manager' => true,
                'approve_hr' => true,
            ],
            [
                'type' => 'cuti melahirkan',
                'is_payed' => true,
                'approve_line' => true,
                'approve_manager' => true,
                'approve_hr' => true,
            ],
            [
                'type' => 'cuti haid',
                'is_payed' => true,
                'approve_line' => true,
                'approve_manager' => true,
                'approve_hr' => true,
            ],
            [
                'type' => 'cuti keguguran',
                'is_payed' => true,
                'approve_line' => true,
                'approve_manager' => true,
                'approve_hr' => true,
            ],
            [
                'type' => 'cuti ibadah haji',
                'is_payed' => true,
                'approve_line' => true,
                'approve_manager' => true,
                'approve_hr' => true,
            ],
            [
                'type' => 'izin koreksi absen',
                'is_payed' => true,
                'approve_line' => true,
                'approve_manager' => true,
                'approve_hr' => true,
            ],
            [
                'type' => 'izin perubahan jam kerja',
                'is_payed' => true,
                'approve_line' => true,
                'approve_manager' => true,
                'approve_hr' => true,
            ],
        ];

        PermitType::insert($type);
    }
}
