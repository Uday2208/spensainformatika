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
    // MANAJEMEN GURU & PLOTTING KELAS
    // ==========================================
    public function dataGuru(Request $request)
    {
        $search = $request->search;
        $query  = User::where('role', 'guru')->with('kelasMengajar');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('username', 'like', "%$search%");
            });
        }

        $gurus = $query->latest()->paginate(20)->withQueryString();
        $allKelas = Kelas::orderBy('nama_kelas', 'asc')->get();

        return view('admin.data-guru', compact('gurus', 'search', 'allKelas'));
    }

    public function storeGuru(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:6',
            'kelas_ids'=> 'nullable|array',
            'kelas_ids.*' => 'exists:kelas,id',
        ]);

        $guru = User::create([
            'name'     => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role'     => 'guru',
        ]);

        if ($request->has('kelas_ids')) {
            $guru->kelasMengajar()->sync($request->kelas_ids);
        }

        return back()->with('success', 'Akun guru berhasil ditambahkan!');
    }

    public function updateGuru(Request $request, $id)
    {
        $guru = User::where('role', 'guru')->findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username,' . $guru->id,
            'kelas_ids'=> 'nullable|array',
            'kelas_ids.*' => 'exists:kelas,id',
        ]);

        $guru->update([
            'name'     => $request->name,
            'username' => $request->username,
        ]);

        if ($request->has('kelas_ids')) {
            $guru->kelasMengajar()->sync($request->kelas_ids);
        }

        return back()->with('success', 'Data guru berhasil diperbarui!');
    }

    public function updateGuruPengampu(Request $request, $id)
    {
        $guru = User::where('role', 'guru')->findOrFail($id);

        $request->validate([
            'kelas_ids'   => 'nullable|array',
            'kelas_ids.*' => 'exists:kelas,id',
        ]);

        $guru->kelasMengajar()->sync($request->input('kelas_ids', []));

        return back()->with('success', "Penugasan kelas untuk {$guru->name} berhasil diperbarui!");
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
    // MANAJEMEN KELAS (DARI GURU CONTROLLER)
    // ==========================================
    public function dataKelas()
    {
        $kelas = Kelas::all();
        return view('admin.data-kelas', compact('kelas'));
    }

    public function storeKelas(Request $request)
    {
        $request->validate([
            'nama_kelas' => 'required|string|max:255',
        ]);

        Kelas::create([
            'nama_kelas' => $request->nama_kelas,
            'tingkat'    => 'VII',
        ]);

        return back()->with('success', 'Data kelas berhasil ditambahkan!');
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
    // MANAJEMEN SISWA (DARI GURU CONTROLLER)
    // ==========================================
    public function dataSiswa(Request $request)
    {
        $kelas = Kelas::withCount('siswas')->get();
        $kelas_id = $request->kelas_id;
        $search = $request->search;
        $selectedKelas = $kelas_id ? Kelas::find($kelas_id) : null;

        if (!$kelas_id && !$search) {
            $siswas = collect();
            return view('admin.data-siswa', compact('siswas', 'kelas', 'kelas_id', 'search', 'selectedKelas'));
        }

        $query = Siswa::with('user', 'kelas')
            ->orderByRaw('CAST(nis AS UNSIGNED) ASC, nis ASC');
        
        if ($kelas_id) {
            $query->where('kelas_id', $kelas_id);
        }
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nis', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Tampilkan seluruh siswa satu kelas dalam satu halaman penuh (misal 32 siswa)
        $siswas = $query->get();
        
        return view('admin.data-siswa', compact('siswas', 'kelas', 'kelas_id', 'search', 'selectedKelas'));
    }

    public function storeSiswa(Request $request)
    {
        $request->validate([
            'nis'      => 'required|unique:siswas,nis',
            'name'     => 'required|string|max:255',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        DB::beginTransaction();
        try {
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

            DB::commit();
            return back()->with('success', 'Data siswa berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['Gagal menambahkan siswa: ' . $e->getMessage()]);
        }
    }

    public function updateSiswa(Request $request, $id)
    {
        $siswa = Siswa::with('user')->findOrFail($id);

        $request->validate([
            'nis'      => 'required|unique:siswas,nis,' . $siswa->id,
            'name'     => 'required|string|max:255',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        DB::beginTransaction();
        try {
            $siswa->update([
                'nis'      => $request->nis,
                'kelas_id' => $request->kelas_id,
            ]);

            if ($siswa->user) {
                $siswa->user->update([
                    'name'     => $request->name,
                    'username' => $request->nis,
                ]);
            }

            DB::commit();
            return back()->with('success', 'Data siswa berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['Gagal memperbarui siswa: ' . $e->getMessage()]);
        }
    }

    public function destroySiswa($id)
    {
        $siswa = Siswa::with('user')->findOrFail($id);
        $nama  = $siswa->user->name ?? $siswa->nis;

        // Menghapus user akan otomatis menghapus siswa karena foreign key cascade
        if ($siswa->user) {
            $siswa->user->delete();
        } else {
            $siswa->delete();
        }

        return back()->with('success', "Data siswa $nama berhasil dihapus!");
    }

    public function resetPasswordSiswa($id)
    {
        $siswa = Siswa::with('user')->findOrFail($id);
        if ($siswa->user) {
            $siswa->user->update([
                'password' => Hash::make($siswa->nis),
                'username' => $siswa->nis,
            ]);
            return back()->with('success', "Username dan Password siswa {$siswa->user->name} berhasil di-reset ke NIS: {$siswa->nis}");
        }
        return back()->withErrors(['Akun user untuk siswa ini tidak ditemukan.']);
    }

    public function importSiswa(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'file'     => 'required|mimes:csv,txt'
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getPathname(), "r");
        
        $header = fgetcsv($handle, 1000, ";");
        
        $rows = [];
        $nisList = [];
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            $nis = trim($data[0] ?? '');
            $nama = trim($data[1] ?? '');
            
            if ($nis && $nama) {
                $rows[] = ['nis' => $nis, 'nama' => $nama];
                $nisList[] = $nis;
            }
        }
        fclose($handle);

        $existingNis = Siswa::whereIn('nis', $nisList)->pluck('nis')->toArray();
        $count = 0;

        DB::beginTransaction();
        try {
            $userInserts = [];
            $newNisList = [];
            $now = now();

            foreach ($rows as $row) {
                if (in_array($row['nis'], $existingNis)) {
                    continue;
                }

                $userInserts[] = [
                    'name'       => $row['nama'],
                    'username'   => $row['nis'],
                    'password'   => Hash::make($row['nis']),
                    'role'       => 'siswa',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                
                $existingNis[] = $row['nis'];
                $newNisList[]  = $row['nis'];
            }

            if (!empty($userInserts)) {
                foreach (array_chunk($userInserts, 100) as $chunk) {
                    User::insert($chunk);
                }

                $createdUsers = User::whereIn('username', $newNisList)
                    ->pluck('id', 'username');

                $siswaInserts = [];
                foreach ($newNisList as $nis) {
                    if (isset($createdUsers[$nis])) {
                        $siswaInserts[] = [
                            'user_id'    => $createdUsers[$nis],
                            'kelas_id'   => $request->kelas_id,
                            'nis'        => $nis,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                        $count++;
                    }
                }

                if (!empty($siswaInserts)) {
                    foreach (array_chunk($siswaInserts, 100) as $chunk) {
                        Siswa::insert($chunk);
                    }
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['Gagal mengimpor data: ' . $e->getMessage()]);
        }

        return back()->with('success', "$count data siswa berhasil diimpor!");
    }

    public function exportSiswa(Request $request)
    {
        $kelas_id = $request->kelas_id;
        if (!$kelas_id) return back()->withErrors(['Kelas harus dipilih untuk export!']);

        $siswas = Siswa::where('kelas_id', $kelas_id)->with('user')->get();
        $kelas = Kelas::find($kelas_id);
        
        $filename = "data_siswa_" . str_replace(' ', '_', $kelas->nama_kelas) . ".csv";
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('Nomer Induk', 'Nama Lengkap');

        $callback = function() use($siswas, $columns) {
            $file = fopen('php://output', 'w');
            
            // Tambahkan BOM agar Excel membaca file sebagai UTF-8 secara native
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, $columns, ';');

            foreach ($siswas as $siswa) {
                fputcsv($file, array($siswa->nis, $siswa->user->name ?? ''), ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ==========================================
    // PENGATURAN WEBSITE
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
