<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambahkan role 'admin' ke kolom enum role di tabel users.
     * Migration ini aman — tidak menghapus atau mengubah data yang sudah ada.
     */
    public function up(): void
    {
        // Ubah enum role untuk menambahkan 'admin'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'guru', 'siswa') DEFAULT 'siswa'");
    }

    /**
     * Kembalikan enum role ke daftar semula (tanpa admin).
     */
    public function down(): void
    {
        // Hapus semua user admin terlebih dahulu agar ENUM bisa di-revert
        DB::table('users')->where('role', 'admin')->delete();
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('guru', 'siswa') DEFAULT 'siswa'");
    }
};
