<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Absensi;
use App\Models\Nilai;
use App\Models\Ujian;
use App\Models\HasilUjian;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AdminController extends Controller
{
    // ==========================================
    // DASHBOARD ADMIN
    // ==========================================
    public function dashboard()
    {
        $totalGuru   = User::where('role', 'guru')->count();
        $totalSiswa  = Siswa::count();
        $totalKelas  = Kelas::count();

        // Statistik presensi hari ini
        $today = now()->toDateString();
        $hadirHariIni = Absensi::where('tanggal', $today)->where('status', 'hadir')->count();
        $sakitHariIni = Absensi::where('tanggal', $today)->where('status', 'sakit')->count();
        $izinHariIni  = Absensi::where('tanggal', $today)->where('status', 'izin')->count();
        $alphaHariIni = Absensi::where('tanggal', $today)->where('status', 'alpha')->count();
        $sudahDiabsen = Absensi::where('tanggal', $today)->exists();

        // Ujian aktif
        $ujianAktif = 0;
        if (Schema::hasTable('ujians')) {
            $ujianAktif = Ujian::where('status', 'aktif')->count();
        }

        // Data grafik kehadiran 7 hari terakhir
        $startDate = now()->subDays(6)->toDateString();
        $agregasi7Hari = Absensi::whereBetween('tanggal', [$startDate, $today])
            ->selectRaw('tanggal, count(*) as total, sum(case when status = "hadir" then 1 else 0 end) as hadir')
            ->groupBy('tanggal')
            ->get()
            ->keyBy('tanggal');

        $chartLabels = [];
        $chartData   = [];
        for ($i = 6; $i >= 0; $i--) {
            $dateObj = now()->subDays($i);
            $dateStr = $dateObj->toDateString();
            $chartLabels[] = $dateObj->translatedFormat('D, d M');

            $stats = $agregasi7Hari->get($dateStr);
            if ($stats && $stats->total > 0) {
                $chartData[] = round(($stats->hadir / $stats->total) * 100);
            } else {
                $chartData[] = 0;
            }
        }

        // Daftar guru
        $guruList = User::where('role', 'guru')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalGuru', 'totalSiswa', 'totalKelas',
            'hadirHariIni', 'sakitHariIni', 'izinHariIni', 'alphaHariIni',
            'sudahDiabsen', 'ujianAktif',
            'chartLabels', 'chartData', 'guruList'
        ));
    }

    // ==========================================
    // MANAJEMEN GURU (CRUD)
    // ==========================================
    public function dataGuru(Request $request)
    {
        $search = $request->search;
        $query  = User::where('role', 'guru');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('username', 'like', "%$search%");
            });
        }

        $gurus = $query->latest()->paginate(20)->withQueryString();
        return view('admin.data-guru', compact('gurus', 'search'));
    }

    public function storeGuru(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'name'     => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role'     => 'guru',
        ]);

        return back()->with('success', 'Akun guru berhasil ditambahkan!');
    }

    public function updateGuru(Request $request, $id)
    {
        $guru = User::where('role', 'guru')->findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username,' . $guru->id,
        ]);

        $guru->update([
            'name'     => $request->name,
            'username' => $request->username,
        ]);

        return back()->with('success', 'Data guru berhasil diperbarui!');
    }

    public function destroyGuru($id)
    {
        $guru = User::where('role', 'guru')->findOrFail($id);
        $guru->delete();

        return back()->with('success', 'Akun guru berhasil dihapus!');
    }

    public function resetPasswordGuru($id)
    {
        $guru = User::where('role', 'guru')->findOrFail($id);
        $defaultPassword = 'guru123';
        $guru->update([
            'password' => Hash::make($defaultPassword),
        ]);

        return back()->with('success', "Password guru {$guru->name} berhasil direset ke: {$defaultPassword}");
    }

    // ==========================================
    // MANAJEMEN KELAS (CRUD — Duplikasi dari GuruController)
    // ==========================================
    public function dataKelas()
    {
        $kelas = Kelas::withCount('siswas')->orderBy('tingkat')->orderBy('nama_kelas')->get();
        return view('admin.data-kelas', compact('kelas'));
    }

    public function storeKelas(Request $request)
    {
        $request->validate([
            'nama_kelas' => 'required|string|max:100',
            'tingkat'    => 'required|string|max:20',
        ]);

        Kelas::create([
            'nama_kelas' => $request->nama_kelas,
            'tingkat'    => $request->tingkat,
        ]);

        return back()->with('success', 'Kelas berhasil ditambahkan!');
    }

    public function destroyKelas($id)
    {
        $kelas = Kelas::findOrFail($id);
        $namaKelas = $kelas->nama_kelas;

        // Cascade: menghapus siswa, absensi, nilai, dll yang terkait
        $kelas->delete();

        return back()->with('success', "Kelas $namaKelas beserta seluruh data siswa di dalamnya berhasil dihapus!");
    }

    // ==========================================
    // MANAJEMEN SISWA (CRUD — Duplikasi dari GuruController)
    // ==========================================
    public function dataSiswa(Request $request)
    {
        $kelas = Kelas::all();
        $kelas_id = $request->kelas_id;
        $search   = $request->search;

        $query = Siswa::with(['user', 'kelas']);

        if ($kelas_id) {
            $query->where('kelas_id', $kelas_id);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nis', 'like', "%$search%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%$search%")
                         ->orWhere('username', 'like', "%$search%");
                  });
            });
        }

        $siswas = $query->orderBy('kelas_id')->latest()->paginate(25)->withQueryString();

        return view('admin.data-siswa', compact('kelas', 'siswas', 'kelas_id', 'search'));
    }

    public function storeSiswa(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'nis'      => 'required|string|max:30|unique:siswas,nis',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'username' => $request->nis,
            'password' => Hash::make($request->nis),
            'role'     => 'siswa',
        ]);

        Siswa::create([
            'user_id'  => $user->id,
            'kelas_id' => $request->kelas_id,
            'nis'      => $request->nis,
        ]);

        return back()->with('success', 'Siswa berhasil ditambahkan! Username & Password default = NIS.');
    }

    public function updateSiswa(Request $request, $id)
    {
        $siswa = Siswa::with('user')->findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:255',
            'nis'      => 'required|string|max:30|unique:siswas,nis,' . $siswa->id,
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $siswa->user->update([
            'name'     => $request->name,
            'username' => $request->nis,
        ]);

        $siswa->update([
            'nis'      => $request->nis,
            'kelas_id' => $request->kelas_id,
        ]);

        return back()->with('success', 'Data siswa berhasil diperbarui!');
    }

    public function destroySiswa($id)
    {
        $siswa = Siswa::with('user')->findOrFail($id);
        $nama  = $siswa->user->name;

        // Hapus user (cascade: siswa, absensi, nilai ikut terhapus)
        $siswa->user->delete();

        return back()->with('success', "Siswa $nama berhasil dihapus!");
    }

    public function resetPasswordSiswa($id)
    {
        $siswa = Siswa::with('user')->findOrFail($id);
        $siswa->user->update([
            'password' => Hash::make($siswa->nis),
        ]);

        return back()->with('success', "Password siswa {$siswa->user->name} berhasil direset ke NIS: {$siswa->nis}");
    }

    public function importSiswa(Request $request)
    {
        $request->validate([
            'file'     => 'required|file|mimes:csv,txt',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $kelas_id = $request->kelas_id;
        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');

        // Skip header row
        $header = fgetcsv($handle, 1000, ',');

        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if (count($row) < 2) continue;

                $nis  = trim($row[0]);
                $name = trim($row[1]);

                if (empty($nis) || empty($name)) continue;

                // Skip jika NIS sudah ada
                if (Siswa::where('nis', $nis)->exists()) {
                    $skipped++;
                    continue;
                }

                // Skip jika username sudah ada
                if (User::where('username', $nis)->exists()) {
                    $skipped++;
                    continue;
                }

                $user = User::create([
                    'name'     => $name,
                    'username' => $nis,
                    'password' => Hash::make($nis),
                    'role'     => 'siswa',
                ]);

                Siswa::create([
                    'user_id'  => $user->id,
                    'kelas_id' => $kelas_id,
                    'nis'      => $nis,
                ]);

                $imported++;
            }

            fclose($handle);
            DB::commit();

            $message = "Berhasil import $imported siswa.";
            if ($skipped > 0) {
                $message .= " ($skipped data dilewati karena NIS sudah ada)";
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return back()->withErrors(['Gagal import: ' . $e->getMessage()]);
        }
    }

    // ==========================================
    // PENGATURAN WEBSITE (Duplikasi dari GuruController)
    // ==========================================
    public function pengaturan()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('admin.pengaturan', compact('settings'));
    }

    public function storePengaturan(Request $request)
    {
        $request->validate([
            'app_name'    => 'nullable|string|max:255',
            'app_subname' => 'nullable|string|max:255',
            'app_logo'    => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        if ($request->hasFile('app_logo')) {
            $oldLogo = Setting::where('key', 'app_logo')->first();
            if ($oldLogo && $oldLogo->value && !str_starts_with($oldLogo->value, 'data:image')) {
                \App\Services\FileStorageService::delete($oldLogo->value, 'logo');
            }

            $logoFileName = \App\Services\FileStorageService::upload($request->file('app_logo'), 'logo');

            Setting::updateOrCreate(
                ['key' => 'app_logo'],
                ['value' => $logoFileName]
            );
            \Illuminate\Support\Facades\Cache::forget('app_settings');
        }

        if ($request->filled('app_name')) {
            Setting::updateOrCreate(
                ['key' => 'app_name'],
                ['value' => $request->app_name]
            );
        }

        if ($request->filled('app_subname')) {
            Setting::updateOrCreate(
                ['key' => 'app_subname'],
                ['value' => $request->app_subname]
            );
        }

        \Illuminate\Support\Facades\Cache::forget('app_settings');

        return back()->with('success', 'Pengaturan aplikasi berhasil diperbarui!');
    }

    public function updateSettingKkm(Request $request)
    {
        $request->validate([
            'kkm_nilai' => 'required|numeric|min:0|max:100',
        ]);

        Setting::updateOrCreate(
            ['key' => 'kkm_nilai'],
            ['value' => $request->kkm_nilai]
        );

        return back()->with('success', 'Nilai KKM berhasil diperbarui!');
    }

    public function updateSettingKomentar(Request $request)
    {
        $request->validate([
            'komentar_homepage_limit' => 'required|integer|min:1|max:500',
        ]);

        Setting::updateOrCreate(
            ['key' => 'komentar_homepage_limit'],
            ['value' => $request->komentar_homepage_limit]
        );

        return back()->with('success', 'Pengaturan komentar berhasil diperbarui!');
    }
}
