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
use App\Models\Tugas;
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
    /**
     * Mengambil daftar kelas yang diampu oleh guru yang sedang login.
     */
    protected function getKelasGuru()
    {
        return \Illuminate\Support\Facades\Auth::user()->kelasMengajar()->orderBy('nama_kelas', 'asc')->get();
    }

    public function dashboard()
    {
        $kelasGuru = $this->getKelasGuru();
        $kelasIds = $kelasGuru->pluck('id');
        $totalKelas = $kelasGuru->count();
        $totalSiswa = Siswa::whereIn('kelas_id', $kelasIds)->count();

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
            'sudahDiabsen',
            'chartLabels', 'chartData'
        ));
    }

    public function absensi(Request $request)
    {
        $kelas     = $this->getKelasGuru();
        $tanggal   = $request->tanggal ?? date('Y-m-d');
        $kelas_id  = $request->kelas_id;

        // Validasi: pastikan kelas yang diminta termasuk kelas yang diampu guru
        if ($kelas_id && !$kelas->contains('id', $kelas_id)) {
            $kelas_id = null;
        }

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
        $kelas = $this->getKelasGuru();
        $kelas_id = $request->kelas_id;
        $tanggal = $request->tanggal ?? date('Y-m-d');
        $pertemuan = $request->pertemuan;

        if ($kelas_id && !$kelas->contains('id', $kelas_id)) {
            $kelas_id = null;
        }

        $siswas = collect();
        $absensi_belum_diisi = false;
        $sudah_dinilai = false;
        $existing_pertemuan = null;

        if ($kelas_id) {
            // Check if attendance has been taken for this class and date
            $cekAbsensi = Absensi::where('tanggal', $tanggal)
                ->whereHas('siswa', function($q) use ($kelas_id) {
                    $q->where('kelas_id', $kelas_id);
                })->exists();

            if (!$cekAbsensi) {
                $absensi_belum_diisi = true;
            } else {
                // Cek apakah nilai keaktifan pada tanggal ini sudah pernah disimpan
                $existingPH = PenilaianHarian::where('kelas_id', $kelas_id)
                    ->where('tanggal', $tanggal)
                    ->first();

                if ($existingPH) {
                    $sudah_dinilai = true;
                    $existing_pertemuan = $existingPH->pertemuan;
                    // Jika user tidak menentukan pertemuan secara spesifik via query parameter, gunakan pertemuan yang tersimpan
                    if (!$pertemuan) {
                        $pertemuan = $existing_pertemuan;
                    }
                }

                if (!$pertemuan) {
                    $pertemuan = '1';
                }

                $siswas = Siswa::where('kelas_id', $kelas_id)->with([
                    'user', 
                    'penilaianHarians' => function($q) use ($tanggal) {
                        $q->where('tanggal', $tanggal);
                    },
                    'absensis' => function($q) use ($tanggal) {
                        $q->where('tanggal', $tanggal);
                    }
                ])->get();
            }
        } else {
            if (!$pertemuan) {
                $pertemuan = '1';
            }
        }

        return view('guru.penilaian-harian', compact('kelas', 'kelas_id', 'tanggal', 'pertemuan', 'siswas', 'absensi_belum_diisi', 'sudah_dinilai', 'existing_pertemuan'));
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
            foreach ($request->nilai as $siswa_id => $nilai) {
                PenilaianHarian::updateOrCreate(
                    [
                        'siswa_id' => $siswa_id,
                        'tanggal' => $request->tanggal,
                    ],
                    [
                        'kelas_id' => $request->kelas_id,
                        'pertemuan' => $request->pertemuan,
                        'nilai' => $nilai,
                        'catatan' => $request->catatan[$siswa_id] ?? null,
                    ]
                );
            }

            \Illuminate\Support\Facades\DB::commit();
            return back()->with('success', 'Penilaian keaktifan berhasil disimpan!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->withErrors(['Terjadi kesalahan saat menyimpan penilaian: ' . $e->getMessage()]);
        }
    }

    /**
     * Memperbarui atau menyimpan satu record nilai keaktifan individu
     */
    public function updatePenilaianHarianSingle(Request $request, $id = null)
    {
        $request->validate([
            'nilai' => 'required|numeric|min:0|max:100',
            'catatan' => 'nullable|string|max:500',
            'siswa_id' => 'nullable|exists:siswas,id',
            'kelas_id' => 'nullable|exists:kelas,id',
            'tanggal' => 'nullable|date',
            'pertemuan' => 'nullable|string|max:50',
        ]);

        if ($id && $id !== 'new') {
            $ph = PenilaianHarian::findOrFail($id);
            $ph->update([
                'nilai' => $request->nilai,
                'catatan' => $request->catatan,
            ]);
            if ($request->filled('tanggal')) $ph->tanggal = $request->tanggal;
            if ($request->filled('pertemuan')) $ph->pertemuan = $request->pertemuan;
            $ph->save();
        } else {
            $ph = PenilaianHarian::updateOrCreate(
                [
                    'siswa_id' => $request->siswa_id,
                    'tanggal' => $request->tanggal ?? date('Y-m-d'),
                ],
                [
                    'kelas_id' => $request->kelas_id,
                    'pertemuan' => $request->pertemuan ?? '1',
                    'nilai' => $request->nilai,
                    'catatan' => $request->catatan,
                ]
            );
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Nilai keaktifan berhasil diperbarui!',
                'data' => $ph
            ]);
        }

        return back()->with('success', 'Nilai keaktifan berhasil diperbarui!');
    }

    /**
     * Menghapus satu data log keaktifan
     */
    public function destroyPenilaianHarianSingle($id)
    {
        $ph = PenilaianHarian::findOrFail($id);
        $ph->delete();

        return back()->with('success', 'Data log keaktifan berhasil dihapus!');
    }

    /**
     * Halaman Rekapitulasi Nilai Keaktifan Siswa (Tampilan Matriks & Log)
     */
    public function rekapKeaktifan(Request $request)
    {
        $kelas = $this->getKelasGuru();
        $kelas_id = $request->kelas_id;
        $nama_siswa = $request->nama_siswa;

        if ($kelas_id && !$kelas->contains('id', $kelas_id)) {
            $kelas_id = null;
        }

        $daftarPertemuan = collect();
        $siswas = collect();
        $stats = null;

        if ($kelas_id) {
            // Ambil daftar pertemuan yang unik di kelas ini
            $daftarPertemuan = PenilaianHarian::where('kelas_id', $kelas_id)
                ->select('pertemuan', DB::raw('MIN(tanggal) as tanggal'))
                ->groupBy('pertemuan')
                ->orderByRaw('CAST(pertemuan AS UNSIGNED) ASC, pertemuan ASC')
                ->get();

            $query = Siswa::where('kelas_id', $kelas_id)
                ->with(['user', 'kelas', 'penilaianHarians' => function($q) use ($kelas_id) {
                    $q->where('kelas_id', $kelas_id)->orderBy('tanggal', 'asc');
                }]);

            if ($nama_siswa) {
                $query->where(function($q) use ($nama_siswa) {
                    $q->where('nis', 'like', "%{$nama_siswa}%")
                      ->orWhereHas('user', function($uq) use ($nama_siswa) {
                          $uq->where('name', 'like', "%{$nama_siswa}%");
                      });
                });
            }

            $siswas = $query->get();

            // Hitung agregasi statistik
            $allPH = PenilaianHarian::where('kelas_id', $kelas_id)->get();
            $totalLog = $allPH->count();
            $rataKelas = $totalLog > 0 ? round($allPH->avg('nilai'), 1) : 0;

            $sangatAktifCount = 0;
            $perluBimbinganCount = 0;

            foreach ($siswas as $s) {
                $avgSiswa = $s->penilaianHarians->avg('nilai');
                if ($avgSiswa !== null) {
                    if ($avgSiswa >= 90) $sangatAktifCount++;
                    if ($avgSiswa < 75) $perluBimbinganCount++;
                }
            }

            $stats = [
                'total_siswa' => $siswas->count(),
                'total_pertemuan' => $daftarPertemuan->count(),
                'total_log' => $totalLog,
                'rata_kelas' => $rataKelas,
                'sangat_aktif' => $sangatAktifCount,
                'perlu_bimbingan' => $perluBimbinganCount,
            ];
        }

        return view('guru.rekap-keaktifan', compact(
            'kelas', 'kelas_id', 'nama_siswa', 'daftarPertemuan', 'siswas', 'stats'
        ));
    }

    /**
     * Ekspor Rekapitulasi Nilai Keaktifan Siswa ke CSV / Excel
     */
    public function exportRekapKeaktifan(Request $request)
    {
        $kelas_id = $request->kelas_id;
        if (!$kelas_id) {
            return back()->withErrors(['Pilih kelas terlebih dahulu untuk mengekspor rekap keaktifan!']);
        }

        $kelas = Kelas::findOrFail($kelas_id);
        $daftarPertemuan = PenilaianHarian::where('kelas_id', $kelas_id)
            ->select('pertemuan', DB::raw('MIN(tanggal) as tanggal'))
            ->groupBy('pertemuan')
            ->orderByRaw('CAST(pertemuan AS UNSIGNED) ASC, pertemuan ASC')
            ->get();

        $siswas = Siswa::where('kelas_id', $kelas_id)
            ->with(['user', 'penilaianHarians' => function($q) use ($kelas_id) {
                $q->where('kelas_id', $kelas_id);
            }])
            ->get();

        $filename = "rekap_keaktifan_" . str_replace(' ', '_', $kelas->nama_kelas) . "_" . date('Ymd_His') . ".csv";
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($siswas, $daftarPertemuan, $kelas) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel

            // Judul Dokumen
            fputcsv($file, ["REKAPITULASI NILAI KEAKTIFAN SISWA - KELAS " . strtoupper($kelas->nama_kelas)], ';');
            fputcsv($file, ["Tanggal Unduh: " . now()->translatedFormat('d F Y H:i')], ';');
            fputcsv($file, [], ';');

            // Header Kolom
            $headers = ['No', 'NIS', 'Nama Siswa', 'Kelas'];
            foreach ($daftarPertemuan as $p) {
                $headers[] = "P-" . $p->pertemuan . " (" . ($p->tanggal ? \Carbon\Carbon::parse($p->tanggal)->format('d/m') : '-') . ")";
            }
            $headers[] = 'Rata-Rata Keaktifan';
            $headers[] = 'Predikat / Kategori';
            fputcsv($file, $headers, ';');

            // Baris Data
            foreach ($siswas as $idx => $siswa) {
                $phMap = $siswa->penilaianHarians->keyBy('pertemuan');
                $avg = $siswa->penilaianHarians->avg('nilai');
                $avgFormatted = $avg !== null ? number_format($avg, 1) : '-';

                $predikat = '-';
                if ($avg !== null) {
                    if ($avg >= 90) $predikat = 'Sangat Aktif (A)';
                    elseif ($avg >= 80) $predikat = 'Aktif (B)';
                    elseif ($avg >= 70) $predikat = 'Cukup (C)';
                    else $predikat = 'Kurang / Pasif (D)';
                }

                $row = [
                    $idx + 1,
                    $siswa->nis,
                    $siswa->user->name ?? '-',
                    $kelas->nama_kelas
                ];

                foreach ($daftarPertemuan as $p) {
                    $item = $phMap->get($p->pertemuan);
                    if ($item) {
                        $val = $item->nilai;
                        if (!empty($item->catatan)) {
                            $val .= " [" . $item->catatan . "]";
                        }
                        $row[] = $val;
                    } else {
                        $row[] = '-';
                    }
                }

                $row[] = $avgFormatted;
                $row[] = $predikat;

                fputcsv($file, $row, ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function nilai()
    {
        $kelas = $this->getKelasGuru();
        $kelas_id = request('kelas_id');

        if ($kelas_id && !$kelas->contains('id', $kelas_id)) {
            $kelas_id = null;
        }

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
        
        $babSudahAdaPerKelas = Nilai::join('siswas', 'nilais.siswa_id', '=', 'siswas.id')
            ->select('siswas.kelas_id', 'nilais.bab')
            ->distinct()
            ->get()
            ->groupBy('kelas_id')
            ->map(fn($items) => $items->pluck('bab')->values()->all())
            ->toArray();

        $kkmSetting = Setting::where('key', 'kkm_nilai')->first();
        $kkm = $kkmSetting ? $kkmSetting->value : 75;

        return view('guru.nilai', compact('kelas', 'siswas', 'riwayat_nilai', 'daftar_bab', 'kkm', 'babSudahAdaPerKelas'));
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
            'p_harian' => 'nullable|numeric|min:0|max:100',
            'p_tugas' => 'nullable|numeric|min:0|max:100',
            'p_quiz' => 'nullable|numeric|min:0|max:100',
            'p_proyek' => 'nullable|numeric|min:0|max:100',
            'p_ulangan' => 'nullable|numeric|min:0|max:100',
        ]);

        $sertakan_ulangan = $request->has('sertakan_ulangan') && $request->sertakan_ulangan == 1;
        $p_ulangan_raw = $sertakan_ulangan ? (float)($request->p_ulangan ?? 0) : 0;
        $p_harian_raw = (float)($request->p_harian ?? 0);
        $p_tugas_raw = (float)($request->p_tugas ?? 0);
        $p_quiz_raw = (float)($request->p_quiz ?? 0);
        $p_proyek_raw = (float)($request->p_proyek ?? 0);

        $totalBobotAktif = $p_harian_raw + $p_tugas_raw + $p_quiz_raw + $p_proyek_raw + $p_ulangan_raw;
        if ($totalBobotAktif <= 0) {
            return back()->withErrors(['Minimal pilih satu komponen penilaian dengan bobot lebih dari 0%.']);
        }

        $siswaIds = array_keys($request->nilai);
        $babReq = trim($request->bab);
        $isForceUpdate = $request->boolean('force_update') || $request->input('mode') === 'update';

        // Check if grades already exist for this class and bab
        $sudahAda = Nilai::whereIn('siswa_id', $siswaIds)
            ->where('bab', $babReq)
            ->exists();

        if ($sudahAda && !$isForceUpdate) {
            return back()->withInput()->withErrors([
                'Nilai untuk Bab "' . $babReq . '" pada kelas ini sudah pernah diinput. Silakan klik tombol "Muat Data" atau gunakan tab "Riwayat & Edit Nilai" jika ingin memperbarui nilai.'
            ]);
        }

        // Pre-calculate rata_harian for these students
        $rataHarianMap = \App\Models\PenilaianHarian::whereIn('siswa_id', $siswaIds)
            ->groupBy('siswa_id')
            ->select('siswa_id', DB::raw('AVG(nilai) as rata_harian'))
            ->pluck('rata_harian', 'siswa_id')
            ->toArray();

        // Pre-calculate rata_ulangan
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
            $tugas = (isset($data['tugas']) && $data['tugas'] !== '' && $p_tugas_raw > 0) ? (float)$data['tugas'] : 0;
            $quiz = (isset($data['quiz']) && $data['quiz'] !== '' && $p_quiz_raw > 0) ? (float)$data['quiz'] : 0;
            $proyek = (isset($data['proyek']) && $data['proyek'] !== '' && $p_proyek_raw > 0) ? (float)$data['proyek'] : 0;
            
            // Hitung rata-rata nilai harian (jika bobot harian aktif)
            if ($p_harian_raw > 0) {
                if (isset($data['harian']) && $data['harian'] !== '' && $data['harian'] !== null) {
                    $rata_harian = (float)$data['harian'];
                } else {
                    $rata_harian = (float)($rataHarianMap[$siswa_id] ?? 80);
                }
            } else {
                $rata_harian = 0;
            }

            // Hitung rata-rata nilai ulangan (jika sertakan ulangan & bobot aktif)
            if ($sertakan_ulangan && $p_ulangan_raw > 0) {
                if (isset($data['ulangan']) && $data['ulangan'] !== '' && $data['ulangan'] !== null && (float)$data['ulangan'] > 0) {
                    $rata_ulangan = (float)$data['ulangan'];
                } else {
                    $rata_ulangan = $rataUlanganMap[$siswa_id] ?? ($existingNilaiMap[$siswa_id] ?? 0);
                }
            } else {
                $rata_ulangan = 0;
            }

            // Hitung nilai akhir ternormalisasi hanya dari bobot komponen yang aktif
            $totalNilaiBobot = ($rata_harian * $p_harian_raw)
                             + ($tugas * $p_tugas_raw)
                             + ($quiz * $p_quiz_raw)
                             + ($proyek * $p_proyek_raw)
                             + ($rata_ulangan * $p_ulangan_raw);

            $nilai_akhir = round($totalNilaiBobot / $totalBobotAktif, 2);

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
            'p_harian' => 'nullable|numeric|min:0|max:100',
            'p_tugas' => 'nullable|numeric|min:0|max:100',
            'p_quiz' => 'nullable|numeric|min:0|max:100',
            'p_proyek' => 'nullable|numeric|min:0|max:100',
            'p_ulangan' => 'nullable|numeric|min:0|max:100',
        ]);

        $sertakan_ulangan = $request->has('sertakan_ulangan') && $request->sertakan_ulangan == 1;
        $p_ulangan_raw = $sertakan_ulangan ? (float)($request->p_ulangan ?? 0) : 0;
        $p_harian_raw = (float)($request->p_harian ?? 0);
        $p_tugas_raw = (float)($request->p_tugas ?? 0);
        $p_quiz_raw = (float)($request->p_quiz ?? 0);
        $p_proyek_raw = (float)($request->p_proyek ?? 0);

        $totalBobotAktif = $p_harian_raw + $p_tugas_raw + $p_quiz_raw + $p_proyek_raw + $p_ulangan_raw;
        if ($totalBobotAktif <= 0) {
            return back()->withErrors(['Minimal pilih satu komponen penilaian dengan bobot lebih dari 0%.']);
        }

        $nilai = Nilai::findOrFail($id);
        
        if ($p_harian_raw > 0) {
            if ($request->has('harian') && $request->harian !== null && $request->harian !== '') {
                $rata_harian = (float)$request->harian;
            } else {
                $rata_harian = PenilaianHarian::where('siswa_id', $nilai->siswa_id)->avg('nilai') ?? 80;
            }
        } else {
            $rata_harian = 0;
        }

        if ($sertakan_ulangan && $p_ulangan_raw > 0) {
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
        } else {
            $rata_ulangan = 0;
        }

        $tugas = $p_tugas_raw > 0 ? (float)$request->tugas : 0;
        $quiz = $p_quiz_raw > 0 ? (float)$request->quiz : 0;
        $proyek = $p_proyek_raw > 0 ? (float)$request->proyek : 0;

        $totalNilaiBobot = ($rata_harian * $p_harian_raw)
                         + ($tugas * $p_tugas_raw)
                         + ($quiz * $p_quiz_raw)
                         + ($proyek * $p_proyek_raw)
                         + ((float)$rata_ulangan * $p_ulangan_raw);

        $nilai_akhir = round($totalNilaiBobot / $totalBobotAktif, 2);

        $nilai->update([
            'bab' => $request->bab,
            'tugas' => round($tugas, 2),
            'quiz' => round($quiz, 2),
            'proyek' => round($proyek, 2),
            'ulangan' => round((float)$rata_ulangan, 2),
            'nilai_akhir' => round($nilai_akhir, 2),
        ]);

        return back()->with('success', 'Nilai berhasil diperbarui!');
    }

    public function destroyNilai($id)
    {
        Nilai::findOrFail($id)->delete();
        return back()->with('success', 'Data nilai berhasil dihapus!');
    }

    public function exportNilai(Request $request)
    {
        $kelas_id = $request->kelas_id;
        if (!$kelas_id) {
            return back()->withErrors(['Pilih kelas terlebih dahulu untuk mengekspor nilai!']);
        }

        $kelas = Kelas::findOrFail($kelas_id);
        $siswas = Siswa::where('kelas_id', $kelas_id)->with(['user', 'nilais'])->get();
        $daftarBab = Nilai::whereIn('siswa_id', $siswas->pluck('id'))->pluck('bab')->unique()->values()->all();

        $kkmSetting = Setting::where('key', 'kkm_nilai')->first();
        $kkm = $kkmSetting ? (float)$kkmSetting->value : 75.0;

        $filename = "rekap_nilai_" . str_replace(' ', '_', $kelas->nama_kelas) . ".csv";
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($siswas, $daftarBab, $kkm) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel

            $columns = array_merge(['No', 'NIS', 'Nama Lengkap'], $daftarBab, ['Rata-rata Rapor', 'Status']);
            fputcsv($file, $columns, ';');

            foreach ($siswas as $index => $siswa) {
                $nilaiMap = $siswa->nilais->pluck('nilai_akhir', 'bab')->toArray();
                $row = [$index + 1, $siswa->nis, $siswa->user->name ?? '-'];

                foreach ($daftarBab as $bab) {
                    $row[] = isset($nilaiMap[$bab]) ? $nilaiMap[$bab] : '-';
                }

                $totalBab = $siswa->nilais->count();
                $rataRata = $totalBab > 0 ? round($siswa->nilais->avg('nilai_akhir'), 1) : 0;
                $row[] = $totalBab > 0 ? $rataRata : '-';
                $row[] = $totalBab > 0 ? ($rataRata >= $kkm ? 'TUNTAS' : 'BELUM TUNTAS') : 'BELUM DINILAI';

                fputcsv($file, $row, ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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

    public function rekapJurnal(Request $request)
    {
        $kelas = $this->getKelasGuru();
        $kelas_id = $request->kelas_id;

        if ($kelas_id && !$kelas->contains('id', $kelas_id)) {
            $kelas_id = null;
        }
        
        if ($kelas_id) {
            try {
                $query = JurnalMengajar::with('kelas')
                    ->where('kelas_id', $kelas_id)
                    ->orderBy('tanggal', 'desc')
                    ->orderBy('pertemuan', 'desc');
                
                $jurnals = $query->paginate(20)->withQueryString();
            } catch (\Exception $e) {
                $jurnals = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            }
        } else {
            $jurnals = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        return view('guru.rekap-jurnal', compact('kelas', 'kelas_id', 'jurnals'));
    }

    /**
     * Mengambil template jurnal mengajar dari pertemuan yang sama di kelas lain
     */
    public function getJurnalTemplate(Request $request)
    {
        $pertemuan = trim($request->pertemuan ?? '');
        $excludeKelasId = $request->exclude_kelas_id;

        if ($pertemuan === '') {
            // Ambil daftar riwayat template unik seluruh pertemuan yang pernah diisi
            $templates = JurnalMengajar::with('kelas')
                ->select('pertemuan', 'materi', 'tujuan_pembelajaran', 'kegiatan', 'tindak_lanjut', 'kelas_id', 'tanggal')
                ->orderBy('tanggal', 'desc')
                ->get()
                ->unique('pertemuan')
                ->values();

            return response()->json([
                'success' => true,
                'templates' => $templates
            ]);
        }

        // Cari data pertemuan spesifik terakhir dari kelas mana pun
        $query = JurnalMengajar::with('kelas')->where('pertemuan', $pertemuan);
        
        if ($excludeKelasId) {
            $query->orderByRaw("CASE WHEN kelas_id != ? THEN 0 ELSE 1 END", [$excludeKelasId]);
        }
        
        $jurnal = $query->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->first();

        if ($jurnal) {
            return response()->json([
                'found' => true,
                'kelas_asal' => $jurnal->kelas->nama_kelas ?? 'Kelas Lain',
                'tanggal_asal' => \Carbon\Carbon::parse($jurnal->tanggal)->translatedFormat('d M Y'),
                'materi' => $jurnal->materi ?? '',
                'tujuan_pembelajaran' => $jurnal->tujuan_pembelajaran ?? '',
                'kegiatan' => $jurnal->kegiatan ?? '',
                'tindak_lanjut' => $jurnal->tindak_lanjut ?? '',
            ]);
        }

        return response()->json(['found' => false]);
    }

    public function storeJurnal(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'tanggal' => 'required|date',
            'pertemuan' => 'required|string|max:50',
            'materi' => 'required|string|max:255',
            'tujuan_pembelajaran' => 'nullable|string',
            'kegiatan' => 'nullable|string',
            'catatan' => 'nullable|string',
            'tindak_lanjut' => 'nullable|string',
        ]);

        try {
            $dataToSave = [
                'materi' => $request->materi,
                'tujuan_pembelajaran' => $request->tujuan_pembelajaran,
                'kegiatan' => $request->kegiatan,
                'catatan' => $request->catatan,
                'tindak_lanjut' => $request->tindak_lanjut,
            ];

            if (Schema::hasColumn('jurnal_mengajars', 'guru_id')) {
                $dataToSave['guru_id'] = auth()->id();
            }

            JurnalMengajar::updateOrCreate(
                [
                    'kelas_id' => $request->kelas_id,
                    'tanggal' => $request->tanggal,
                    'pertemuan' => $request->pertemuan,
                ],
                $dataToSave
            );

            return back()->with('success', 'Jurnal mengajar berhasil disimpan!');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['Gagal menyimpan jurnal mengajar: ' . $e->getMessage()]);
        }
    }

    public function destroyJurnal($id)
    {
        $jurnal = JurnalMengajar::findOrFail($id);
        $jurnal->delete();

        return back()->with('success', 'Jurnal mengajar berhasil dihapus!');
    }

    public function exportRekapJurnal(Request $request)
    {
        $kelas_id = $request->kelas_id;
        $query = JurnalMengajar::with('kelas')->orderBy('tanggal', 'asc')->orderBy('pertemuan', 'asc');
        
        $filename_part = "semua_kelas";
        if ($kelas_id) {
            $query->where('kelas_id', $kelas_id);
            $kelas = Kelas::find($kelas_id);
            if ($kelas) $filename_part = str_replace(' ', '_', $kelas->nama_kelas);
        }

        $jurnals = $query->get();
        $filename = "rekap_jurnal_mengajar_" . $filename_part . ".csv";

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('No', 'Tanggal', 'Kelas', 'Pertemuan Ke', 'Materi / Topik', 'Tujuan Pembelajaran', 'Kegiatan Pembelajaran', 'Catatan / Kejadian', 'Tindak Lanjut');

        $callback = function() use($jurnals, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
            fputcsv($file, $columns, ';');

            foreach ($jurnals as $index => $j) {
                fputcsv($file, array(
                    $index + 1,
                    \Carbon\Carbon::parse($j->tanggal)->translatedFormat('d F Y'),
                    $j->kelas->nama_kelas ?? '-',
                    'Ke-' . $j->pertemuan,
                    $j->materi ?: '-',
                    $j->tujuan_pembelajaran ?: '-',
                    $j->kegiatan ?: '-',
                    $j->catatan ?: '-',
                    $j->tindak_lanjut ?: '-'
                ), ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function rekapAbsensi(Request $request)
    {
        $kelas = $this->getKelasGuru();
        $kelas_id = $request->kelas_id;
        $nama_siswa = $request->nama_siswa;

        if ($kelas_id && !$kelas->contains('id', $kelas_id)) {
            $kelas_id = null;
        }

        if (!$kelas_id && !$nama_siswa) {
            $siswas = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 35);
            $stats = null;
            return view('guru.rekap-absensi', compact('kelas', 'kelas_id', 'nama_siswa', 'siswas', 'stats'));
        }
        
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
        
        // Ringkasan Agregat Statistik untuk Kelas/Filter yang dipilih
        $allSiswasQuery = clone $query;
        $allSiswas = $allSiswasQuery->get();
        
        $totalHadir = $allSiswas->sum('hadir_count');
        $totalSakit = $allSiswas->sum('sakit_count');
        $totalIzin = $allSiswas->sum('izin_count');
        $totalDispen = $allSiswas->sum('dispen_count');
        $totalAlpha = $allSiswas->sum('alpha_count');
        $grandTotal = $totalHadir + $totalSakit + $totalIzin + $totalDispen + $totalAlpha;
        $avgKehadiran = $grandTotal > 0 ? round(($totalHadir / $grandTotal) * 100, 1) : 0;

        $stats = [
            'total_siswa' => $allSiswas->count(),
            'total_hadir' => $totalHadir,
            'total_sakit' => $totalSakit,
            'total_izin' => $totalIzin,
            'total_dispen' => $totalDispen,
            'total_alpha' => $totalAlpha,
            'avg_kehadiran' => $avgKehadiran,
        ];
        
        $siswas = $query->paginate(35)->withQueryString();

        return view('guru.rekap-absensi', compact('kelas', 'kelas_id', 'nama_siswa', 'siswas', 'stats'));
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

    // ==========================================
    // TUGAS KELAS (ROMBEL)
    // ==========================================
    public function tugasKelas(Request $request)
    {
        $kelas = $this->getKelasGuru();

        $query = Tugas::where('guru_id', auth()->id())
            ->where('tipe_target', 'kelas')
            ->with(['kelases', 'kelas']);

        if ($request->filled('kelas_id')) {
            $selectedKelas = $request->kelas_id;
            $query->where(function($q) use ($selectedKelas) {
                $q->whereHas('kelases', function($qk) use ($selectedKelas) {
                    $qk->where('kelas.id', $selectedKelas);
                })->orWhere('kelas_id', $selectedKelas);
            });
        }

        $tugasList = $query->latest()->paginate(15)->withQueryString();

        return view('guru.tugas-kelas', compact('tugasList', 'kelas'));
    }

    public function storeTugasKelas(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'kelas_ids' => 'required|array|min:1',
            'kelas_ids.*' => 'exists:kelas,id',
            'deadline' => 'nullable|date',
            'deskripsi' => 'nullable|string',
            'link' => 'nullable|url',
            'file_tugas' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar,jpg,jpeg,png|max:10240',
        ]);

        $data = [
            'guru_id' => auth()->id(),
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'tipe_target' => 'kelas',
            'kelas_id' => $request->kelas_ids[0] ?? null,
            'deadline' => $request->deadline,
            'link' => $request->link,
        ];

        if ($request->hasFile('file_tugas')) {
            $data['file_tugas'] = \App\Services\FileStorageService::upload($request->file('file_tugas'), 'tugas');
        }

        $tugas = Tugas::create($data);
        $tugas->kelases()->sync($request->kelas_ids);

        return back()->with('success', 'Tugas kelas berhasil diterbitkan untuk rombel yang dipilih!');
    }

    // ==========================================
    // TUGAS INDIVIDU (SISWA)
    // ==========================================
    public function tugasIndividu(Request $request)
    {
        $kelas = $this->getKelasGuru();
        $kelasIds = $kelas->pluck('id');

        $siswas = Siswa::whereIn('kelas_id', $kelasIds)
            ->with(['user', 'kelas'])
            ->get()
            ->sortBy(['kelas.nama_kelas', 'user.name'])
            ->values();

        $query = Tugas::where('guru_id', auth()->id())
            ->where('tipe_target', 'individu')
            ->with(['siswas.user', 'siswas.kelas', 'siswa.user', 'siswa.kelas']);

        if ($request->filled('kelas_id')) {
            $selectedKelas = $request->kelas_id;
            $query->where(function($q) use ($selectedKelas) {
                $q->whereHas('siswas', function($qs) use ($selectedKelas) {
                    $qs->where('kelas_id', $selectedKelas);
                })->orWhere('kelas_id', $selectedKelas);
            });
        }

        $tugasList = $query->latest()->paginate(15)->withQueryString();

        return view('guru.tugas-individu', compact('tugasList', 'kelas', 'siswas'));
    }

    public function storeTugasIndividu(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'exists:siswas,id',
            'deadline' => 'nullable|date',
            'deskripsi' => 'nullable|string',
            'link' => 'nullable|url',
            'file_tugas' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar,jpg,jpeg,png|max:10240',
        ]);

        $firstSiswa = Siswa::find($request->siswa_ids[0]);

        $data = [
            'guru_id' => auth()->id(),
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'tipe_target' => 'individu',
            'kelas_id' => $firstSiswa ? $firstSiswa->kelas_id : null,
            'siswa_id' => $request->siswa_ids[0] ?? null,
            'deadline' => $request->deadline,
            'link' => $request->link,
        ];

        if ($request->hasFile('file_tugas')) {
            $data['file_tugas'] = \App\Services\FileStorageService::upload($request->file('file_tugas'), 'tugas');
        }

        $tugas = Tugas::create($data);
        $tugas->siswas()->sync($request->siswa_ids);

        return back()->with('success', 'Tugas individu berhasil dibagikan ke siswa terpilih!');
    }

    public function destroyTugas($id)
    {
        $tugas = Tugas::where('id', $id)->where('guru_id', auth()->id())->firstOrFail();

        if ($tugas->file_tugas) {
            \App\Services\FileStorageService::delete($tugas->file_tugas, 'tugas');
        }

        $tugas->delete();

        return back()->with('success', 'Tugas berhasil dihapus!');
    }
}
