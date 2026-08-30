<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Membuat akun admin default jika belum ada.
     */
    public function run(): void
    {
        if (!User::where('role', 'admin')->exists()) {
            User::create([
                'name'     => 'Administrator',
                'username' => 'admin',
                'password' => Hash::make('admin123'),
                'role'     => 'admin',
            ]);

            $this->command->info('Akun Admin default berhasil dibuat. Username: admin | Password: admin123');
        } else {
            $this->command->info('Akun Admin sudah ada, tidak perlu membuat ulang.');
        }
    }
}
