<?php

namespace Database\Seeders;

use App\Models\Lembaga;
use App\Models\Siswa;
use Illuminate\Database\Seeder;

class SiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $latis = Lembaga::where('name', 'Latiseducation')->first();
        $tutor = Lembaga::where('name', 'Tutorindonesia')->first();

        $students = [
            [
                'lembaga_id' => $latis ? $latis->id : 1,
                'nis' => '10012001',
                'nama' => 'Ahmad Fauzi',
                'email' => 'ahmad.fauzi@example.com',
                'foto' => null,
            ],
            [
                'lembaga_id' => $latis ? $latis->id : 1,
                'nis' => '10012002',
                'nama' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@example.com',
                'foto' => null,
            ],
            [
                'lembaga_id' => $latis ? $latis->id : 1,
                'nis' => '10012003',
                'nama' => 'Budi Pratama',
                'email' => 'budi.pratama@example.com',
                'foto' => null,
            ],
            [
                'lembaga_id' => $tutor ? $tutor->id : 2,
                'nis' => '20012001',
                'nama' => 'Dewi Sartika',
                'email' => 'dewi.sartika@example.com',
                'foto' => null,
            ],
            [
                'lembaga_id' => $tutor ? $tutor->id : 2,
                'nis' => '20012002',
                'nama' => 'Rizky Ramadhan',
                'email' => 'rizky.ramadhan@example.com',
                'foto' => null,
            ],
            [
                'lembaga_id' => $tutor ? $tutor->id : 2,
                'nis' => '20012003',
                'nama' => 'Putri Ayu Lestari',
                'email' => 'putri.ayu@example.com',
                'foto' => null,
            ],
            [
                'lembaga_id' => $latis ? $latis->id : 1,
                'nis' => '10012004',
                'nama' => 'Fajar Maulana',
                'email' => 'fajar.maulana@example.com',
                'foto' => null,
            ],
            [
                'lembaga_id' => $tutor ? $tutor->id : 2,
                'nis' => '20012004',
                'nama' => 'Anisa Rahmawati',
                'email' => 'anisa.rahma@example.com',
                'foto' => null,
            ],
        ];

        foreach ($students as $data) {
            Siswa::updateOrCreate(
                ['nis' => $data['nis']],
                $data
            );
        }
    }
}
