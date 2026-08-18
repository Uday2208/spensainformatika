<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Absensi;
use App\Models\Nilai;
use App\Models\User;
use App\Models\Komentar;
use App\Models\Setting;
use App\Models\Materi;
use App\Models\Artikel;
use App\Models\PenilaianHarian;
use App\Models\JurnalMengajar;
use App\Models\Ujian;
use App\Models\HasilUjian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class GuruController extends Controller
{
    public function dashboard()
    {
        $totalSiswa = Siswa::count();
        $totalKelas = Kelas::count();

        $today = now()->toDateString();
        
        $agregasiHariIni = Absensi::where('tanggal', $today)
            ->selectRaw('count(*) as total, 
                         sum(case when status = "hadir" then 1 else 0 end) as hadir,
                         sum(case when status = "sakit" then 1 else 0 end) as sakit,
                         sum(case when status = "izin" then 1 else 0 end) as izin,
                         sum(case when status = "alpha" then 1 else 0 end) as alpha')
            ->first();

        $sudahDiabsen  = (int)($agregasiHariIni->total ?? 0);
        $hadirHariIni  = (int)($agregasiHariIni->hadir ?? 0);
        $sakitHariIni  = (int)($agregasiHariIni->sakit ?? 0);
        $izinHariIni   = (int)($agregasiHariIni->izin ?? 0);
        $alphaHariIni  = (int)($agregasiHariIni->alpha ?? 0);

        // Komentar baru (pending belum dilihat)
        $totalKomentar = Komentar::count();

        // Data grafik kehadiran 7 hari terakhir (dari DB nyata)
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

        return view('guru.dashboard', compact(
            'totalSiswa', 'totalKelas',
            'hadirHariIni', 'sakitHariIni', 'izinHariIni', 'alphaHariIni',
            'sudahDiabsen', 'totalKomentar',
            'chartLabels', 'chartData'
        ));
    }

    public function absensi(Request $request)
    {
        $kelas     = Kelas::all();
        $tanggal   = $request->tanggal ?? date('Y-m-d');
        $kelas_id  = $request->kelas_id;

        $siswas = collect();
        $existingAbsensi = collect();
        $sudahDiisi = false;

        if ($kelas_id) {
            // Ambil siswa beserta data absensi untuk kelas dan tanggal yang dipilih
            $siswas = Siswa::where('kelas_id', $kelas_id)->with(['kelas', 'user'])->get();
            $siswaIds = $siswas->pluck('id');
            $existingAbsensi = Absensi::where('tanggal', $tanggal)
                ->whereIn('siswa_id', $siswaIds)
                ->pluck('status', 'siswa_id'); // [siswa_id => status]

            $sudahDiisi = $existingAbsensi->count() > 0;
        }

        return view('guru.absensi', compact('kelas', 'siswas', 'tanggal', 'kelas_id', 'existingAbsensi', 'sudahDiisi'));
    }

    public function storeAbsensi(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'absensi' => 'required|array',
        ]);

        $upsertData = [];
        $now = now();
        
        foreach ($request->absensi as $siswa_id => $status) {
            $upsertData[] = [
                'siswa_id' => $siswa_id,
                'tanggal' => $request->tanggal,
                'status' => $status,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($upsertData)) {
            Absensi::upsert(
                $upsertData,
                ['siswa_id', 'tanggal'],
                ['status', 'updated_at']
            );
        }

        return back()->with('success', 'Absensi berhasil disimpan!');
    }

    public function destroyAbsensiByDate(Request $request)
    {
        $request->validate([
            'tanggal_hapus' => 'required|date',
            'kelas_id_hapus' => 'required',
        ]);

        $tanggal = $request->tanggal_hapus;
        $kelas_id = $request->kelas_id_hapus;

        $query = Absensi::where('tanggal', $tanggal);
        
        if ($kelas_id !== 'all') {
            $query->whereHas('siswa', function($q) use ($kelas_id) {
                $q->where('kelas_id', $kelas_id);
            });
        }

        $count = $query->count();
        $query->delete();

        // Optional: Also delete related daily assessments if needed
        // PenilaianHarian::where('tanggal', $tanggal)->...

        return back()->with('success', "Berhasil menghapus $count data kehadiran pada tanggal " . \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') . "!");
    }

    public function penilaianHarian(Request $request)
    {
        $kelas = Kelas::all();
        $kelas_id = $request->kelas_id;
        $tanggal = $request->tanggal ?? date('Y-m-d');
        $pertemuan = $request->pertemuan ?? '1';

        $siswas = collect();
        $absensi_belum_diisi = false;

        if ($kelas_id) {
            // Check if attendance has been taken for this class and date
            $cekAbsensi = Absensi::where('tanggal', $tanggal)
                ->whereHas('siswa', function($q) use ($kelas_id) {
                    $q->where('kelas_id', $kelas_id);
                })->exists();

            if (!$cekAbsensi) {
                $absensi_belum_diisi = true;
            } else {
                $siswas = Siswa::where('kelas_id', $kelas_id)->with([
                    'user', 
                    'penilaianHarians' => function($q) use ($tanggal, $pertemuan) {
                        $q->where('tanggal', $tanggal)->where('pertemuan', $pertemuan);
                    },
                    'absensis' => function($q) use ($tanggal) {
                        $q->where('tanggal', $tanggal);
                    }
                ])->get();
            }
        }

        return view('guru.penilaian-harian', compact('kelas', 'kelas_id', 'tanggal', 'pertemuan', 'siswas', 'absensi_belum_diisi'));
    }

    public function storePenilaianHarian(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'tanggal' => 'required|date',
            'pertemuan' => 'required|string',
            'nilai' => 'required|array',
        ]);

        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            $upsertData = [];
            $now = now();

            foreach ($request->nilai as $siswa_id => $nilai) {
                $upsertData[] = [
                    'siswa_id' => $siswa_id,
                    'kelas_id' => $request->kelas_id,
                    'tanggal' => $request->tanggal,
                    'pertemuan' => $request->pertemuan,
                    'nilai' => $nilai,
                    'catatan' => $request->catatan[$siswa_id] ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!empty($upsertData)) {
                PenilaianHarian::upsert(
                    $upsertData,
                    ['siswa_id', 'tanggal', 'pertemuan'],
                    ['kelas_id', 'nilai', 'catatan', 'updated_at']
                );
            }

            \Illuminate\Support\Facades\DB::commit();
            return back()->with('success', 'Penilaian harian berhasil disimpan!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->withErrors(['Terjadi kesalahan saat menyimpan penilaian: ' . $e->getMessage()]);
        }
    }

    public function nilai()
    {
        $kelas = Kelas::all();
        $kelas_id = request('kelas_id');

        if ($kelas_id) {
            $siswas = Siswa::with('kelas', 'nilais')->where('kelas_id', $kelas_id)->get();
            $riwayat_nilai = Nilai::with(['siswa.user', 'siswa.kelas'])
                ->whereHas('siswa', function ($q) use ($kelas_id) {
                    $q->where('kelas_id', $kelas_id);
                })
                ->orderBy('created_at', 'desc')
                ->get();
                
            $siswaIds = $siswas->pluck('id')->toArray();
            $rataHarianAll = DB::table('penilaian_harians')
                ->whereIn('siswa_id', $siswaIds)
                ->groupBy('siswa_id')
                ->select('siswa_id', DB::raw('AVG(nilai) as rata_harian'))
                ->pluck('rata_harian', 'siswa_id')
                ->toArray();
        } else {
            $siswas = collect();
            $riwayat_nilai = collect();
            $rataHarianAll = [];
        }

        foreach ($siswas as $s) {
            $s->rata_harian = round((float)($rataHarianAll[$s->id] ?? 80), 1);
        }

        // Fetch unique bab from both Nilai and Ujian for filter datalist
        $babNilai = Nilai::select('bab')->pluck('bab');
        $babUjian = Schema::hasTable('ujians') ? Ujian::select('bab')->pluck('bab') : collect([]);
        
        $daftar_bab = $babNilai
            ->concat($babUjian)
            ->filter()
            ->unique()
            ->values();
        
        $kkmSetting = Setting::where('key', 'kkm_nilai')->first();
        $kkm = $kkmSetting ? $kkmSetting->value : 75;
        
        return view('guru.nilai', compact('kelas', 'siswas', 'riwayat_nilai', 'daftar_bab', 'kkm'));
    }

    public function getRataUjian(Request $request)
    {
        $bab = trim($request->bab ?? '');

        // Fetch from hasil_ujians with flexible matching (bab, lowercase, contains, or judul)
        $rataFromUjian = [];
        if (Schema::hasTable('hasil_ujians') && Schema::hasTable('ujians')) {
            $queryUjian = DB::table('hasil_ujians')
                ->join('ujians', 'hasil_ujians.ujian_id', '=', 'ujians.id')
                ->whereIn('hasil_ujians.status', ['selesai', 'dinilai', 'mengerjakan']);

            if ($bab !== '') {
                $queryUjian->where(function($q) use ($bab) {
                    $q->where('ujians.bab', $bab)
                      ->orWhere(DB::raw('LOWER(ujians.bab)'), strtolower($bab))
                      ->orWhere('ujians.bab', 'LIKE', '%' . $bab . '%')
                      ->orWhere('ujians.judul', 'LIKE', '%' . $bab . '%');
                });
            }

            $rataFromUjian = $queryUjian
                ->groupBy('hasil_ujians.siswa_id')
                ->select('hasil_ujians.siswa_id', DB::raw('AVG(hasil_ujians.nilai_akhir) as rata_nilai'))
                ->pluck('rata_nilai', 'siswa_id')
                ->toArray();

            // If empty and $bab was provided, try fetching without bab filter as fallback
            if (empty($rataFromUjian)) {
                $rataFromUjian = DB::table('hasil_ujians')
                    ->groupBy('siswa_id')
                    ->select('siswa_id', DB::raw('AVG(nilai_akhir) as rata_nilai'))
                    ->pluck('rata_nilai', 'siswa_id')
                    ->toArray();
            }
        }

        // Fetch fallback from nilais.ulangan
        $queryNilais = DB::table('nilais')
            ->whereNotNull('ulangan')
            ->where('ulangan', '>', 0);

        if ($bab !== '') {
            $queryNilais->where(function($q) use ($bab) {
                $q->where('bab', $bab)
                  ->orWhere(DB::raw('LOWER(bab)'), strtolower($bab))
                  ->orWhere('bab', 'LIKE', '%' . $bab . '%');
            });
        }

        $rataFromNilais = $queryNilais->pluck('ulangan', 'siswa_id')->toArray();

        // Merge results giving priority to hasil_ujians
        $merged = array_replace($rataFromNilais, $rataFromUjian);
        $formatted = collect($merged)->map(fn($val) => round((float)$val, 2));

        return response()->json($formatted);
    }

    public function storeNilai(Request $request)
    {
        $request->validate([
            'bab' => 'required|string',
            'nilai' => 'required|array',
            'p_harian' => 'required|numeric|min:0|max:100',
            'p_tugas' => 'required|numeric|min:0|max:100',
            'p_quiz' => 'required|numeric|min:0|max:100',
            'p_proyek' => 'required|numeric|min:0|max:100',
            'p_ulangan' => 'nullable|numeric|min:0|max:100',
        ]);

        $sertakan_ulangan = $request->has('sertakan_ulangan') && $request->sertakan_ulangan == 1;
        $p_ulangan_raw = $sertakan_ulangan ? (float)($request->p_ulangan ?? 0) : 0;

        $totalBobot = round((float)$request->p_harian + (float)$request->p_tugas + (float)$request->p_quiz + (float)$request->p_proyek + $p_ulangan_raw, 2);
        if ($totalBobot != 100) {
            return back()->withErrors(['Total persentase bobot harus tepat 100%']);
        }

        $p_harian = $request->p_harian / 100;
        $p_tugas = $request->p_tugas / 100;
        $p_quiz = $request->p_quiz / 100;
        $p_proyek = $request->p_proyek / 100;
        $p_ulangan = $p_ulangan_raw / 100;

        $siswaIds = array_keys($request->nilai);

        // Pre-calculate rata_harian for these students
        $rataHarianMap = \App\Models\PenilaianHarian::whereIn('siswa_id', $siswaIds)
            ->groupBy('siswa_id')
            ->select('siswa_id', DB::raw('AVG(nilai) as rata_harian'))
            ->pluck('rata_harian', 'siswa_id')
            ->toArray();

        // Pre-calculate rata_ulangan
        $babReq = trim($request->bab);
        $rataUlanganMap = [];
        
        if (Schema::hasTable('hasil_ujians') && Schema::hasTable('ujians')) {
            $queryUlangan = DB::table('hasil_ujians')
                ->join('ujians', 'hasil_ujians.ujian_id', '=', 'ujians.id')
                ->whereIn('hasil_ujians.siswa_id', $siswaIds)
                ->whereIn('hasil_ujians.status', ['selesai', 'dinilai']);

            if ($babReq !== '') {
                $queryUlangan->where(function($q) use ($babReq) {
                    $q->where('ujians.bab', $babReq)
                      ->orWhere(DB::raw('LOWER(ujians.bab)'), strtolower($babReq))
                      ->orWhere('ujians.bab', 'LIKE', '%' . $babReq . '%')
                      ->orWhere('ujians.judul', 'LIKE', '%' . $babReq . '%');
                });
            }

            $rataUlanganMap = $queryUlangan
                ->groupBy('hasil_ujians.siswa_id')
                ->select('hasil_ujians.siswa_id', DB::raw('AVG(hasil_ujians.nilai_akhir) as rata_ulangan'))
                ->pluck('rata_ulangan', 'siswa_id')
                ->toArray();
        }

        // Pre-fetch existing nilai fallback
        $existingNilaiMap = \App\Models\Nilai::whereIn('siswa_id', $siswaIds)
            ->where('bab', $request->bab)
            ->pluck('ulangan', 'siswa_id')
            ->toArray();

        $upsertData = [];
        $now = now();

        foreach ($request->nilai as $siswa_id => $data) {
            $tugas = (isset($data['tugas']) && $data['tugas'] !== '') ? (float)$data['tugas'] : 0;
            $quiz = (isset($data['quiz']) && $data['quiz'] !== '') ? (float)$data['quiz'] : 0;
            $proyek = (isset($data['proyek']) && $data['proyek'] !== '') ? (float)$data['proyek'] : 0;
            
            // Hitung rata-rata nilai harian
            if (isset($data['harian']) && $data['harian'] !== '' && $data['harian'] !== null) {
                $rata_harian = (float)$data['harian'];
            } else {
                $rata_harian = (float)($rataHarianMap[$siswa_id] ?? 80);
            }

            // Hitung rata-rata nilai ulangan (prioritaskan input form jika diisi/ditarik)
            if (isset($data['ulangan']) && $data['ulangan'] !== '' && $data['ulangan'] !== null && (float)$data['ulangan'] > 0) {
                $rata_ulangan = (float)$data['ulangan'];
            } else {
                $rata_ulangan = $rataUlanganMap[$siswa_id] ?? ($existingNilaiMap[$siswa_id] ?? 0);
            }

            if ($sertakan_ulangan) {
                $nilai_akhir = ($rata_harian * $p_harian) + ($tugas * $p_tugas) + ($quiz * $p_quiz) + ($proyek * $p_proyek) + ($rata_ulangan * $p_ulangan);
            } else {
                $nilai_akhir = ($rata_harian * $p_harian) + ($tugas * $p_tugas) + ($quiz * $p_quiz) + ($proyek * $p_proyek);
            }

            $upsertData[] = [
                'siswa_id' => $siswa_id,
                'bab' => $request->bab,
                'tugas' => round($tugas, 2),
                'quiz' => round($quiz, 2),
                'proyek' => round($proyek, 2),
                'ulangan' => round((float)$rata_ulangan, 2),
                'nilai_akhir' => round($nilai_akhir, 2),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($upsertData)) {
            \App\Models\Nilai::upsert(
                $upsertData,
                ['siswa_id', 'bab'], // requires unique index nilais_siswa_id_bab_unique
                ['tugas', 'quiz', 'proyek', 'ulangan', 'nilai_akhir', 'updated_at']
            );
        }

        return back()->with('success', 'Nilai berhasil disimpan!');
    }

    public function updateNilai(Request $request, $id)
    {
        $request->validate([
            'bab' => 'required|string',
            'harian' => 'nullable|numeric|min:0|max:100',
            'tugas' => 'required|numeric|min:0|max:100',
            'quiz' => 'required|numeric|min:0|max:100',
            'proyek' => 'required|numeric|min:0|max:100',
            'ulangan' => 'nullable|numeric|min:0|max:100',
            'p_harian' => 'required|numeric|min:0|max:100',
            'p_tugas' => 'required|numeric|min:0|max:100',
            'p_quiz' => 'required|numeric|min:0|max:100',
            'p_proyek' => 'required|numeric|min:0|max:100',
            'p_ulangan' => 'nullable|numeric|min:0|max:100',
        ]);

        $sertakan_ulangan = $request->has('sertakan_ulangan') && $request->sertakan_ulangan == 1;
        $p_ulangan_raw = $sertakan_ulangan ? (float)($request->p_ulangan ?? 0) : 0;

        $totalBobot = round((float)$request->p_harian + (float)$request->p_tugas + (float)$request->p_quiz + (float)$request->p_proyek + $p_ulangan_raw, 2);
        if ($totalBobot != 100) {
            return back()->withErrors(['Total persentase bobot harus tepat 100%']);
        }

        $p_harian = $request->p_harian / 100;
        $p_tugas = $request->p_tugas / 100;
        $p_quiz = $request->p_quiz / 100;
        $p_proyek = $request->p_proyek / 100;
        $p_ulangan = $p_ulangan_raw / 100;

        $nilai = Nilai::findOrFail($id);
        
        if ($request->has('harian') && $request->harian !== null && $request->harian !== '') {
            $rata_harian = (float)$request->harian;
        } else {
            $rata_harian = PenilaianHarian::where('siswa_id', $nilai->siswa_id)->avg('nilai') ?? 80;
        }

        if ($request->has('ulangan') && $request->ulangan !== null && $request->ulangan !== '') {
            $rata_ulangan = (float)$request->ulangan;
        } else {
            $babReq = trim($request->bab);
            $queryUlangan = DB::table('hasil_ujians')
                ->join('ujians', 'hasil_ujians.ujian_id', '=', 'ujians.id')
                ->where('hasil_ujians.siswa_id', $nilai->siswa_id)
                ->whereIn('hasil_ujians.status', ['selesai', 'dinilai']);

            if ($babReq !== '') {
                $queryUlangan->where(function($q) use ($babReq) {
                    $q->where('ujians.bab', $babReq)
                      ->orWhere(DB::raw('LOWER(ujians.bab)'), strtolower($babReq))
                      ->orWhere('ujians.bab', 'LIKE', '%' . $babReq . '%')
                      ->orWhere('ujians.judul', 'LIKE', '%' . $babReq . '%');
                });
            }

            $rata_ulangan = $queryUlangan->avg('hasil_ujians.nilai_akhir');

            if ($rata_ulangan === null) {
                $rata_ulangan = $nilai->ulangan ?? 0;
            }
        }

        if ($sertakan_ulangan) {
            $nilai_akhir = ($rata_harian * $p_harian) + ($request->tugas * $p_tugas) + ($request->quiz * $p_quiz) + ($request->proyek * $p_proyek) + ($rata_ulangan * $p_ulangan);
        } else {
            $nilai_akhir = ($rata_harian * $p_harian) + ($request->tugas * $p_tugas) + ($request->quiz * $p_quiz) + ($request->proyek * $p_proyek);
        }

        $nilai->update([
            'bab' => $request->bab,
            'tugas' => $request->tugas,
            'quiz' => $request->quiz,
            'proyek' => $request->proyek,
            'ulangan' => round($rata_ulangan, 2),
            'nilai_akhir' => round($nilai_akhir, 2),
        ]);

        return back()->with('success', 'Nilai berhasil diperbarui!');
    }

    public function destroyNilai($id)
    {
        Nilai::findOrFail($id)->delete();
        return back()->with('success', 'Data nilai berhasil dihapus!');
    }

    public function destroyNilaiByBab(Request $request)
    {
        $request->validate([
            'bab' => 'required|string',
        ]);

        $bab = trim($request->bab);
        $deletedCount = Nilai::where(function($q) use ($bab) {
            $q->where('bab', $bab)
              ->orWhere(DB::raw('LOWER(bab)'), strtolower($bab));
        })->delete();

        if ($deletedCount > 0) {
            return back()->with('success', "Berhasil menghapus $deletedCount data nilai untuk Materi/Bab: '$bab'!");
        }

        return back()->withErrors(["Tidak ada data nilai yang ditemukan untuk Materi/Bab: '$bab'."]);
    }

    public function dataSiswa(\Illuminate\Http\Request $request)
    {
        $query = Siswa::with('user', 'kelas');
        
        $kelas_id = $request->kelas_id;
        $search = $request->search;
        
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
        
        $siswas = $query->paginate(25)->withQueryString();
        $kelas = Kelas::all();
        
        return view('guru.data-siswa', compact('siswas', 'kelas'));
    }

    public function storeSiswa(Request $request)
    {
        $request->validate([
            'nis' => 'required|string|unique:siswas,nis',
            'name' => 'required|string|max:255',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        // Create User for login (username and password = NIS)
        $user = User::create([
            'name' => $request->name,
            'username' => $request->nis,
            'password' => Hash::make($request->nis),
            'role' => 'siswa'
        ]);

        // Create Siswa profile
        Siswa::create([
            'user_id' => $user->id,
            'kelas_id' => $request->kelas_id,
            'nis' => $request->nis,
        ]);

        return back()->with('success', 'Data siswa berhasil ditambahkan! Siswa dapat login dengan NISN sebagai username dan password.');
    }

    public function updateSiswa(Request $request, $id)
    {
        $request->validate([
            'nis' => 'required|string|unique:siswas,nis,' . $id,
            'name' => 'required|string|max:255',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $siswa = Siswa::findOrFail($id);
        $user = User::findOrFail($siswa->user_id);

        $user->update([
            'name' => $request->name,
            'username' => $request->nis,
        ]);

        $siswa->update([
            'nis' => $request->nis,
            'kelas_id' => $request->kelas_id,
        ]);

        // Optional: If password needs to be updated when NIS changes, uncomment this:
        // $user->update(['password' => Hash::make($request->nis)]);

        return back()->with('success', 'Data siswa berhasil diperbarui!');
    }

    public function destroySiswa($id)
    {
        $siswa = Siswa::findOrFail($id);
        $user_id = $siswa->user_id;
        $siswa->delete();
        $user = User::find($user_id);
        if ($user) {
            $user->delete();
        }

        return back()->with('success', 'Data siswa berhasil dihapus!');
    }

    public function resetPasswordSiswa($id)
    {
        $siswa = Siswa::findOrFail($id);
        $user = User::findOrFail($siswa->user_id);

        $user->update([
            'username' => $siswa->nis,
            'password' => Hash::make($siswa->nis)
        ]);

        return back()->with('success', 'Username dan Password siswa ' . $user->name . ' berhasil direset ke NIS: ' . $siswa->nis);
    }

    public function dataKelas()
    {
        $kelas = Kelas::all();
        return view('guru.data-kelas', compact('kelas'));
    }

    public function storeKelas(Request $request)
    {
        $request->validate([
            'nama_kelas' => 'required|string|max:255',
        ]);
        
        Kelas::create([
            'nama_kelas' => $request->nama_kelas,
            'tingkat' => 'VII'
        ]);
        return back()->with('success', 'Data kelas berhasil ditambahkan!');
    }

    public function destroyKelas($id)
    {
        Kelas::findOrFail($id)->delete();
        return back()->with('success', 'Data kelas berhasil dihapus!');
    }

    public function importSiswa(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'file' => 'required|mimes:csv,txt'
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

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $userInserts = [];
            $newNisList = [];
            $now = now();

            foreach ($rows as $row) {
                if (in_array($row['nis'], $existingNis)) {
                    continue;
                }

                $userInserts[] = [
                    'name' => $row['nama'],
                    'username' => $row['nis'],
                    'password' => \Illuminate\Support\Facades\Hash::make($row['nis']),
                    'role' => 'siswa',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                
                $existingNis[] = $row['nis']; // In case of duplicates in the CSV
                $newNisList[] = $row['nis'];
            }

            if (!empty($userInserts)) {
                foreach (array_chunk($userInserts, 100) as $chunk) {
                    User::insert($chunk);
                }

                // Fetch created users to map user_id for siswas
                $createdUsers = User::whereIn('username', $newNisList)
                    ->pluck('id', 'username');

                $siswaInserts = [];
                foreach ($newNisList as $nis) {
                    if (isset($createdUsers[$nis])) {
                        $siswaInserts[] = [
                            'user_id' => $createdUsers[$nis],
                            'kelas_id' => $request->kelas_id,
                            'nis' => $nis,
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
            \Illuminate\Support\Facades\DB::commit();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
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

    public function kelolaKomentar()
    {
        $komentars = Komentar::with(['siswa.user', 'siswa.kelas'])->latest()->paginate(50)->withQueryString();
        $limitSetting = Setting::where('key', 'komentar_homepage_limit')->first();
        $limit = $limitSetting ? $limitSetting->value : 50;

        return view('guru.kelola-komentar', compact('komentars', 'limit'));
    }

    public function destroyKomentar($id)
    {
        Komentar::findOrFail($id)->delete();
        return back()->with('success', 'Komentar berhasil dihapus!');
    }

    public function updateSettingKomentar(Request $request)
    {
        $request->validate([
            'limit' => 'required|integer|min:1|max:500'
        ]);

        Setting::updateOrCreate(
            ['key' => 'komentar_homepage_limit'],
            ['value' => $request->limit]
        );

        \Illuminate\Support\Facades\Cache::forget('app_settings');

        return back()->with('success', 'Pengaturan limit komentar berhasil disimpan!');
    }

    public function updateSettingKkm(Request $request)
    {
        $request->validate([
            'kkm' => 'required|numeric|min:0|max:100'
        ]);

        Setting::updateOrCreate(
            ['key' => 'kkm_nilai'],
            ['value' => $request->kkm]
        );

        \Illuminate\Support\Facades\Cache::forget('app_settings');

        return back()->with('success', 'Nilai KKM berhasil diperbarui!');
    }

    public function rekapJurnal(Request $request)
    {
        $kelas = Kelas::all();
        $kelas_id = $request->kelas_id;
        
        try {
            $query = JurnalMengajar::with('kelas')->orderBy('tanggal', 'desc');
            
            if ($kelas_id) {
                $query->where('kelas_id', $kelas_id);
            }
            
            $jurnals = $query->paginate(25)->withQueryString();
        } catch (\Exception $e) {
            $jurnals = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 25);
        }

        return view('guru.rekap-jurnal', compact('kelas', 'kelas_id', 'jurnals'));
    }

    public function rekapAbsensi(Request $request)
    {
        $kelas = Kelas::all();
        $kelas_id = $request->kelas_id;
        $nama_siswa = $request->nama_siswa;
        
        $query = Siswa::with('user', 'kelas')
            ->withCount([
                'absensis as hadir_count' => function ($q) { $q->where('status', 'hadir'); },
                'absensis as sakit_count' => function ($q) { $q->where('status', 'sakit'); },
                'absensis as izin_count' => function ($q) { $q->where('status', 'izin'); },
                'absensis as dispen_count' => function ($q) { $q->where('status', 'dispen'); },
                'absensis as alpha_count' => function ($q) { $q->where('status', 'alpha'); }
            ]);
        
        if ($kelas_id) {
            $query->where('kelas_id', $kelas_id);
        }
        
        if ($nama_siswa) {
            $query->whereHas('user', function($q) use ($nama_siswa) {
                $q->where('name', 'like', '%' . $nama_siswa . '%');
            });
        }
        
        $siswas = $query->paginate(25)->withQueryString();

        return view('guru.rekap-absensi', compact('kelas', 'kelas_id', 'nama_siswa', 'siswas'));
    }

    public function exportRekapAbsensi(Request $request)
    {
        $kelas_id = $request->kelas_id;
        $nama_siswa = $request->nama_siswa;
        
        $query = Siswa::with('user')
            ->withCount([
                'absensis as hadir_count' => function ($q) { $q->where('status', 'hadir'); },
                'absensis as sakit_count' => function ($q) { $q->where('status', 'sakit'); },
                'absensis as izin_count' => function ($q) { $q->where('status', 'izin'); },
                'absensis as dispen_count' => function ($q) { $q->where('status', 'dispen'); },
                'absensis as alpha_count' => function ($q) { $q->where('status', 'alpha'); }
            ]);
            
        $filename_part = "semua";
        
        if ($kelas_id) {
            $query->where('kelas_id', $kelas_id);
            $kelas = Kelas::find($kelas_id);
            if ($kelas) $filename_part = str_replace(' ', '_', $kelas->nama_kelas);
        }
        
        if ($nama_siswa) {
            $query->whereHas('user', function($q) use ($nama_siswa) {
                $q->where('name', 'like', '%' . $nama_siswa . '%');
            });
            $filename_part .= "_filter_nama";
        }

        $siswas = $query->get();
        $filename = "rekap_absensi_" . $filename_part . ".csv";
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('No', 'Nomer Induk', 'Nama Lengkap', 'Hadir', 'Sakit', 'Izin', 'Dispen', 'Alpha');

        $callback = function() use($siswas, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM
            fputcsv($file, $columns, ';');

            foreach ($siswas as $index => $siswa) {
                $hadir = $siswa->hadir_count;
                $sakit = $siswa->sakit_count;
                $izin = $siswa->izin_count;
                $dispen = $siswa->dispen_count;
                $alpha = $siswa->alpha_count;
                
                fputcsv($file, array($index + 1, $siswa->nis, $siswa->user->name ?? '', $hadir, $sakit, $izin, $dispen, $alpha), ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function rekapAbsensiSiswa($id)
    {
        $siswa = Siswa::with(['user', 'kelas', 'absensis' => function($q) {
            $q->orderBy('tanggal', 'desc');
        }])->findOrFail($id);
        
        return view('guru.rekap-absensi-detail', compact('siswa'));
    }

    public function exportRekapAbsensiSiswa($id)
    {
        $siswa = Siswa::with(['user', 'absensis' => function($q) {
            $q->orderBy('tanggal', 'desc');
        }])->findOrFail($id);

        $filename = "rekap_detail_" . str_replace(' ', '_', $siswa->user->name ?? $siswa->nis) . ".csv";
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('No', 'Tanggal', 'Status Kehadiran');

        $callback = function() use($siswa, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM
            fputcsv($file, $columns, ';');

            foreach ($siswa->absensis as $index => $absensi) {
                fputcsv($file, array(
                    $index + 1, 
                    \Carbon\Carbon::parse($absensi->tanggal)->translatedFormat('d F Y'), 
                    ucfirst($absensi->status)
                ), ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function materi()
    {
        $materis = Materi::latest()->paginate(20);
        return view('guru.materi', compact('materis'));
    }

    public function storeMateri(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'link' => 'nullable|url',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'file_materi' => 'nullable|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,zip,rar|max:5120'
        ]);

        $data = $request->only(['judul', 'deskripsi', 'link']);

        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $fotoName = time() . '_foto_' . $foto->getClientOriginalName();
            $foto->move(public_path('uploads/materi'), $fotoName);
            $data['foto'] = $fotoName;
        }

        if ($request->hasFile('file_materi')) {
            $file = $request->file('file_materi');
            $fileName = time() . '_file_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/materi'), $fileName);
            $data['file_materi'] = $fileName;
        }

        Materi::create($data);

        return back()->with('success', 'Materi berhasil diunggah!');
    }

    public function destroyMateri($id)
    {
        $materi = Materi::findOrFail($id);

        if ($materi->foto) {
            $fotoPath = public_path('uploads/materi/' . $materi->foto);
            if (File::exists($fotoPath)) File::delete($fotoPath);
        }

        if ($materi->file_materi) {
            $filePath = public_path('uploads/materi/' . $materi->file_materi);
            if (File::exists($filePath)) File::delete($filePath);
        }

        $materi->delete();

        return back()->with('success', 'Materi berhasil dihapus!');
    }

    public function artikel()
    {
        $artikels = Artikel::latest()->paginate(20);
        return view('guru.artikel', compact('artikels'));
    }

    public function storeArtikel(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'gambar.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,jfif,avif|max:5120'
        ]);

        $data = [
            'judul' => $request->judul,
            'slug' => \Illuminate\Support\Str::slug($request->judul),
            'konten' => $request->konten,
        ];

        $uploadedImages = [];
        if ($request->hasFile('gambar')) {
            $uploadDir = public_path('uploads/artikel');
            if (!File::exists($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true, true);
            }

            foreach ($request->file('gambar') as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($uploadDir, $filename);
                $uploadedImages[] = $filename;
            }
        }

        if (count($uploadedImages) > 0) {
            $data['gambar'] = $uploadedImages;
        }

        Artikel::create($data);

        return back()->with('success', 'Artikel berhasil dipublikasikan!');
    }

    public function updateArtikel(Request $request, $id)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'gambar.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,jfif,avif|max:5120'
        ]);

        $artikel = Artikel::findOrFail($id);

        $artikel->judul = $request->judul;
        if (\Illuminate\Support\Facades\Schema::hasColumn('artikels', 'slug')) {
            $artikel->slug = \Illuminate\Support\Str::slug($request->judul);
        }
        $artikel->konten = $request->konten;

        if ($request->hasFile('gambar')) {
            $uploadDir = public_path('uploads/artikel');
            if (!File::exists($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true, true);
            }

            // Hapus gambar lama jika ada
            if ($artikel->gambar && is_array($artikel->gambar)) {
                foreach ($artikel->gambar as $img) {
                    $imgPath = $uploadDir . '/' . $img;
                    if (File::exists($imgPath)) {
                        File::delete($imgPath);
                    }
                }
            }

            $uploadedImages = [];
            foreach ($request->file('gambar') as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($uploadDir, $filename);
                $uploadedImages[] = $filename;
            }

            $artikel->gambar = $uploadedImages;
        }

        $artikel->save();

        return back()->with('success', 'Artikel berhasil diperbarui!');
    }

    public function destroyArtikel($id)
    {
        $artikel = Artikel::findOrFail($id);

        if ($artikel->gambar && is_array($artikel->gambar)) {
            foreach ($artikel->gambar as $img) {
                $path = public_path('uploads/artikel/' . $img);
                if (File::exists($path)) File::delete($path);
            }
        }

        $artikel->delete();

        return back()->with('success', 'Artikel berhasil dihapus!');
    }

    public function pengaturan()
    {
        $settings = \App\Models\Setting::pluck('value', 'key')->toArray();
        return view('guru.pengaturan', compact('settings'));
    }

    public function storePengaturan(Request $request)
    {
        $request->validate([
            'app_name' => 'nullable|string|max:255',
            'app_subname' => 'nullable|string|max:255',
            'app_logo' => 'nullable|image|mimes:png,jpg,jpeg|max:2048'
        ]);

        if ($request->hasFile('app_logo')) {
            $logo = $request->file('app_logo');
            $mime = $logo->getMimeType();
            $base64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logo->getRealPath()));

            \App\Models\Setting::updateOrCreate(
                ['key' => 'app_logo'],
                ['value' => $base64]
            );
            \Illuminate\Support\Facades\Cache::forget('app_settings');
        }

        if ($request->filled('app_name')) {
            \App\Models\Setting::updateOrCreate(
                ['key' => 'app_name'],
                ['value' => $request->app_name]
            );
        }

        if ($request->filled('app_subname')) {
            \App\Models\Setting::updateOrCreate(
                ['key' => 'app_subname'],
                ['value' => $request->app_subname]
            );
        }

        // Clear cached settings so changes take effect immediately
        \Illuminate\Support\Facades\Cache::forget('app_settings');

        return back()->with('success', 'Pengaturan aplikasi berhasil diperbarui!');
    }

    // =========================================================
    // (Rollback: Removed jurnalMengajar methods to restore original state)
}
