<?php

namespace Database\Seeders;

use App\Models\Lembaga;
use Illuminate\Database\Seeder;

class LembagaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lembagas = [
            [
                'name' => 'Latiseducation',
                'code' => 'latis',
            ],
            [
                'name' => 'Tutorindonesia',
                'code' => 'tutor',
            ],
        ];

        foreach ($lembagas as $item) {
            Lembaga::updateOrCreate(
                ['name' => $item['name']],
                ['code' => $item['code']]
            );
        }
    }
}
