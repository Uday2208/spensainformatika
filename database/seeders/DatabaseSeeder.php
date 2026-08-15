<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Buat akun Guru
        \App\Models\User::create([
            'name' => 'Pak Guru Informatika',
            'username' => 'guru',
            'role' => 'guru',
            'password' => bcrypt('password'),
        ]);

        // Buat Kelas
        $kelas = \App\Models\Kelas::create([
            'nama_kelas' => 'X RPL 1',
            'tingkat' => '10'
        ]);

        // Buat akun Siswa
        $userSiswa = \App\Models\User::create([
            'name' => 'Budi Siswa',
            'username' => 'siswa',
            'role' => 'siswa',
            'password' => bcrypt('password'),
        ]);

        // Hubungkan Siswa dengan Kelas dan User
        \App\Models\Siswa::create([
            'user_id' => $userSiswa->id,
            'kelas_id' => $kelas->id,
            'nis' => '10012023',
        ]);
    }
}
