<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Ujian;
use App\Models\Soal;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\HasilUjian;
use App\Models\JawabanSiswa;
use App\Models\LogUjian;
use App\Models\Setting;
use App\Models\Nilai;

class UjianController extends Controller
{
    public function index(Request $request)
    {
        $query = Ujian::with('kelasList')->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $ujians = $query->paginate(20)->withQueryString();
        return view('guru.ujian.index', compact('ujians'));
    }

    public function create()
    {
        $daftar_bab = \App\Models\Nilai::select('bab')->distinct()->orderBy('bab')->pluck('bab');
        return view('guru.ujian.create', compact('daftar_bab'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'bab' => 'required|string|max:255',
        ]);

        $ujian = Ujian::create([
            'judul' => $request->judul,
            'bab' => $request->bab,
            'status' => 'draft',
            'durasi' => 60,
        ]);

        return redirect('/app/ujian/' . $ujian->id)->with('success', 'Ujian berhasil dibuat! Silakan tambahkan soal terlebih dahulu, lalu atur Tanggal, Durasi, Token, dan Kelas di Setting Ujian.');
    }

    public function show($id)
    {
        $ujian = Ujian::with(['kelasList', 'soals'])->findOrFail($id);
        $allKelas = Kelas::all();
        return view('guru.ujian.show', compact('ujian', 'allKelas'));
    }

    public function update(Request $request, $id)
    {
        $ujian = Ujian::findOrFail($id);

        if (!$ujian->isDraft()) {
            return back()->withErrors(['Ujian yang sudah aktif/selesai tidak bisa diedit.']);
        }

        $request->validate([
            'judul' => 'required|string|max:255',
            'bab' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'durasi' => 'required|integer|min:5|max:180',
            'kelas_ids' => 'required|array|min:1',
            'kelas_ids.*' => 'exists:kelas,id',
        ]);

        $ujian->update([
            'judul' => $request->judul,
            'bab' => $request->bab,
            'tanggal' => $request->tanggal,
            'durasi' => $request->durasi,
        ]);

        $ujian->kelasList()->sync($request->kelas_ids);

        return back()->with('success', 'Ujian berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $ujian = Ujian::findOrFail($id);

        if (!$ujian->isDraft()) {
            return back()->withErrors(['Hanya ujian berstatus draft yang bisa dihapus.']);
        }

        $ujian->delete();
        return redirect('/app/ujian')->with('success', 'Ujian berhasil dihapus!');
    }

    // ==================== SOAL ====================

    public function storeSoal(Request $request, $id)
    {
        $ujian = Ujian::findOrFail($id);

        if (!$ujian->isDraft()) {
            return back()->withErrors(['Tidak bisa menambah soal saat ujian aktif/selesai.']);
        }

        $rules = [
            'tipe' => 'required|in:pg,essay',
            'pertanyaan' => 'required|string',
            'bobot' => 'required|integer|min:1|max:100',
        ];

        if ($request->tipe === 'pg') {
            $rules['opsi_a'] = 'required|string';
            $rules['opsi_b'] = 'required|string';
            $rules['opsi_c'] = 'required|string';
            $rules['opsi_d'] = 'required|string';
            $rules['jawaban_benar'] = 'required|in:a,b,c,d';
        }

        $request->validate($rules);

        $maxUrutan = $ujian->soals()->max('urutan') ?? 0;

        Soal::create([
            'ujian_id' => $ujian->id,
            'tipe' => $request->tipe,
            'pertanyaan' => $request->pertanyaan,
            'opsi_a' => $request->opsi_a,
            'opsi_b' => $request->opsi_b,
            'opsi_c' => $request->opsi_c,
            'opsi_d' => $request->opsi_d,
            'jawaban_benar' => $request->jawaban_benar,
            'bobot' => $request->bobot,
            'urutan' => $maxUrutan + 1,
        ]);

        return back()->with('success', 'Soal berhasil ditambahkan!');
    }

    public function updateSoal(Request $request, $id, $soalId)
    {
        $ujian = Ujian::findOrFail($id);

        if (!$ujian->isDraft()) {
            return back()->withErrors(['Tidak bisa mengedit soal saat ujian aktif/selesai.']);
        }

        $soal = Soal::where('ujian_id', $id)->findOrFail($soalId);

        $rules = [
            'pertanyaan' => 'required|string',
            'bobot' => 'required|integer|min:1|max:100',
        ];

        if ($soal->tipe === 'pg') {
            $rules['opsi_a'] = 'required|string';
            $rules['opsi_b'] = 'required|string';
            $rules['opsi_c'] = 'required|string';
            $rules['opsi_d'] = 'required|string';
            $rules['jawaban_benar'] = 'required|in:a,b,c,d';
        }

        $request->validate($rules);

        $updateData = [
            'pertanyaan' => $request->pertanyaan,
            'bobot' => $request->bobot,
        ];

        if ($soal->tipe === 'pg') {
            $updateData['opsi_a'] = $request->opsi_a;
            $updateData['opsi_b'] = $request->opsi_b;
            $updateData['opsi_c'] = $request->opsi_c;
            $updateData['opsi_d'] = $request->opsi_d;
            $updateData['jawaban_benar'] = $request->jawaban_benar;
        }

        $soal->update($updateData);

        return back()->with('success', 'Soal berhasil diperbarui!');
    }

    public function destroySoal($id, $soalId)
    {
        $ujian = Ujian::findOrFail($id);

        if (!$ujian->isDraft()) {
            return back()->withErrors(['Tidak bisa menghapus soal saat ujian aktif/selesai.']);
        }

        Soal::where('ujian_id', $id)->findOrFail($soalId)->delete();
        return back()->with('success', 'Soal berhasil dihapus!');
    }

    // ==================== STATUS ====================

    public function activate($id)
    {
        $ujian = Ujian::findOrFail($id);

        if (!$ujian->isDraft()) {
            return back()->withErrors(['Ujian sudah aktif atau selesai.']);
        }

        if ($ujian->soals()->count() === 0) {
            return back()->withErrors(['Tambahkan minimal 1 soal sebelum mengaktifkan ujian.']);
        }

        if ($ujian->kelasList()->count() === 0) {
            return back()->withErrors(['Pilih minimal 1 kelas target di Setting Ujian sebelum mengaktifkan ujian.']);
        }

        if (!$ujian->token) {
            $ujian->generateToken();
        }

        $ujian->update(['status' => 'aktif']);

        return back()->with('success', 'Ujian berhasil diaktifkan! Token: ' . $ujian->token);
    }

    public function saveSetting(Request $request, $id)
    {
        $ujian = Ujian::findOrFail($id);

        $request->validate([
            'tanggal' => 'nullable|date',
            'durasi' => 'required|integer|min:5|max:180',
            'kelas_ids' => 'nullable|array',
            'kelas_ids.*' => 'exists:kelas,id',
            'token_option' => 'required|in:keep,random,custom',
            'custom_token' => 'nullable|string|size:6',
            'status' => 'required|in:draft,aktif,selesai',
        ]);

        if ($request->status === 'aktif') {
            if ($ujian->soals()->count() === 0) {
                return back()->withErrors(['Tambahkan minimal 1 soal sebelum mengaktifkan ujian.']);
            }
            if (empty($request->kelas_ids) || count($request->kelas_ids) === 0) {
                return back()->withErrors(['Pilih minimal 1 kelas target yang diaktifkan untuk ujian ini.']);
            }
        }

        $updateData = [
            'tanggal' => $request->tanggal,
            'durasi' => $request->durasi,
            'status' => $request->status,
        ];

        // Handle Token logic
        if ($request->token_option === 'random') {
            $updateData['token'] = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6));
        } elseif ($request->token_option === 'custom' && $request->filled('custom_token')) {
            $updateData['token'] = strtoupper(trim($request->custom_token));
        } elseif (!$ujian->token) {
            $updateData['token'] = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6));
        }

        $ujian->update($updateData);

        // Sync kelas list
        $kelasIds = $request->input('kelas_ids', []);
        $ujian->kelasList()->sync($kelasIds);

        return back()->with('success', 'Setting Ujian berhasil diperbarui!');
    }

    public function finish($id)
    {
        $ujian = Ujian::findOrFail($id);

        if (!$ujian->isAktif()) {
            return back()->withErrors(['Hanya ujian aktif yang bisa diakhiri.']);
        }

        $hasilMengerjakan = HasilUjian::where('ujian_id', $id)->where('status', 'mengerjakan')->get();
        
        if ($hasilMengerjakan->count() > 0) {
            $soalPg = $ujian->soals()->where('tipe', 'pg')->get();
            $totalBobotPg = $soalPg->sum('bobot');
            $hasEssay = $ujian->soalEssay()->exists();
            
            $siswaIds = $hasilMengerjakan->pluck('siswa_id')->toArray();
            $allJawaban = JawabanSiswa::where('ujian_id', $id)
                ->whereIn('siswa_id', $siswaIds)
                ->get();
                
            $jawabanMap = [];
            foreach ($allJawaban as $j) {
                $jawabanMap[$j->siswa_id . '_' . $j->soal_id] = $j;
            }
            
            $correctIds = [];
            $incorrectIds = [];
            $hasilUpdates = [];
            $now = now();

            DB::beginTransaction();
            try {
                foreach ($hasilMengerjakan as $hasil) {
                    $pgData = $this->hitungNilaiPg($ujian, $hasil, $soalPg, $totalBobotPg, $hasEssay, $jawabanMap, $correctIds, $incorrectIds);
                    
                    $hasilUpdates[] = [
                        'id' => $hasil->id,
                        'siswa_id' => $hasil->siswa_id,
                        'ujian_id' => $hasil->ujian_id,
                        'nilai_pg' => $pgData['nilai_pg'],
                        'nilai_akhir' => $pgData['nilai_akhir'] ?? $hasil->nilai_akhir,
                        'nilai_essay' => $hasil->nilai_essay,
                        'status' => 'selesai',
                        'finished_at' => $now,
                        'started_at' => $hasil->started_at,
                        'tab_switch_count' => $hasil->tab_switch_count,
                        'created_at' => $hasil->created_at,
                        'updated_at' => $now,
                    ];
                }
                
                if (!empty($correctIds)) {
                    JawabanSiswa::whereIn('id', $correctIds)->update(['is_correct' => true]);
                }
                if (!empty($incorrectIds)) {
                    JawabanSiswa::whereIn('id', $incorrectIds)->update(['is_correct' => false]);
                }
                
                if (!empty($hasilUpdates)) {
                    HasilUjian::upsert(
                        $hasilUpdates,
                        ['id'],
                        ['status', 'nilai_pg', 'nilai_akhir', 'finished_at', 'updated_at']
                    );
                }
                
                $ujian->update(['status' => 'selesai']);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->withErrors(['Terjadi kesalahan: ' . $e->getMessage()]);
            }
        } else {
            $ujian->update(['status' => 'selesai']);
        }

        return back()->with('success', 'Ujian berhasil diakhiri! ' . $hasilMengerjakan->count() . ' siswa di-auto-submit.');
    }

    // ==================== MONITORING ====================

    public function monitoring($id)
    {
        $ujian = Ujian::with('kelasList')->findOrFail($id);
        
        $hasilUjians = HasilUjian::where('ujian_id', $id)
            ->with(['siswa.user', 'siswa.kelas'])
            ->paginate(25)
            ->withQueryString();

        $totalSoal = $ujian->soals()->count();

        // Hitung progress per siswa dengan 1 query
        $siswaIds = $hasilUjians->pluck('siswa_id')->toArray();
        $jawabanCounts = JawabanSiswa::where('ujian_id', $id)
            ->whereIn('siswa_id', $siswaIds)
            ->whereNotNull('jawaban')
            ->where('jawaban', '!=', '')
            ->selectRaw('siswa_id, count(*) as count')
            ->groupBy('siswa_id')
            ->pluck('count', 'siswa_id');

        foreach ($hasilUjians as $hasil) {
            $hasil->jawaban_count = $jawabanCounts[$hasil->siswa_id] ?? 0;
        }

        $logs = LogUjian::where('ujian_id', $id)
            ->with(['siswa.user'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('guru.ujian.monitoring', compact('ujian', 'hasilUjians', 'totalSoal', 'logs'));
    }

    // ==================== KOREKSI ====================

    public function koreksiEssay($id)
    {
        $ujian = Ujian::with('soals')->findOrFail($id);

        if (!$ujian->isSelesai()) {
            return back()->withErrors(['Akhiri ujian terlebih dahulu sebelum koreksi.']);
        }

        $soalEssay = $ujian->soals()->where('tipe', 'essay')->get();

        $hasilUjians = HasilUjian::where('ujian_id', $id)
            ->with(['siswa.user', 'siswa.kelas'])
            ->orderByRaw("CASE WHEN status = 'dinilai' THEN 1 ELSE 0 END ASC")
            ->orderBy('created_at', 'desc')
            ->get();

        // Get all essay answers
        $jawabanEssay = JawabanSiswa::where('ujian_id', $id)
            ->whereHas('soal', fn($q) => $q->where('tipe', 'essay'))
            ->get()
            ->groupBy('siswa_id');

        $kkmSetting = Setting::where('key', 'kkm_nilai')->first();
        $kkm = $kkmSetting ? $kkmSetting->value : 75;

        return view('guru.ujian.koreksi', compact('ujian', 'soalEssay', 'hasilUjians', 'jawabanEssay', 'kkm'));
    }

    public function storeKoreksi(Request $request, $id)
    {
        $ujian = Ujian::findOrFail($id);

        if (!$ujian->isSelesai()) {
            return back()->withErrors(['Ujian belum selesai.']);
        }

        $request->validate([
            'nilai_essay' => 'required|array',
            'nilai_essay.*' => 'required|numeric|min:0|max:100',
        ]);

        $totalBobot = $ujian->totalBobot();
        $bobotPg = $ujian->soalPg()->sum('bobot');
        $bobotEssay = $ujian->soalEssay()->sum('bobot');

        $siswaIds = array_keys($request->nilai_essay);
        $hasilMap = HasilUjian::where('ujian_id', $id)
            ->whereIn('siswa_id', $siswaIds)
            ->get()
            ->keyBy('siswa_id');

        DB::beginTransaction();
        try {
            foreach ($request->nilai_essay as $siswa_id => $nilaiEssay) {
                $hasil = $hasilMap->get($siswa_id);

                if ($hasil) {
                    // nilai_akhir = weighted average
                    $nilaiAkhir = 0;
                    if ($totalBobot > 0) {
                        $nilaiAkhir = (($hasil->nilai_pg * $bobotPg) + ($nilaiEssay * $bobotEssay)) / $totalBobot;
                    }

                    $hasil->update([
                        'nilai_essay' => $nilaiEssay,
                        'nilai_akhir' => round($nilaiAkhir, 2),
                        'status' => 'dinilai',
                    ]);
                }
            }

            DB::commit();
            return back()->with('success', 'Nilai essay berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function finalisasi($id)
    {
        $ujian = Ujian::findOrFail($id);

        if (!$ujian->isSelesai()) {
            return back()->withErrors(['Ujian belum selesai.']);
        }

        $hasilUjians = HasilUjian::where('ujian_id', $id)->get();
        $hasEssay = $ujian->soalEssay()->count() > 0;

        // Jika ada essay, pastikan sudah dikoreksi
        if ($hasEssay) {
            $belumDinilai = $hasilUjians->where('status', 'selesai')->count();
            if ($belumDinilai > 0) {
                return back()->withErrors(["Masih ada $belumDinilai siswa yang belum dikoreksi essay-nya."]);
            }
        } else {
            // Jika hanya PG, set status ke dinilai dan nilai_akhir = nilai_pg
            foreach ($hasilUjians->where('status', 'selesai') as $h) {
                $h->update([
                    'status'      => 'dinilai',
                    'nilai_akhir' => $h->nilai_pg,
                ]);
            }
        }

        $kkmSetting = Setting::where('key', 'kkm_nilai')->first();
        $kkm = $kkmSetting ? $kkmSetting->value : 75;

        // Simpan nilai_akhir ke tabel nilais (kolom ulangan) – updateOrCreate agar tidak duplikat
        DB::beginTransaction();
        try {
            $savedCount = 0;
            foreach (HasilUjian::where('ujian_id', $id)->where('status', 'dinilai')->get() as $hasil) {
                Nilai::updateOrCreate(
                    ['siswa_id' => $hasil->siswa_id, 'bab' => $ujian->bab],
                    ['ulangan' => $hasil->nilai_akhir]
                );
                $savedCount++;
            }

            DB::commit();
            return back()->with('success', "Nilai ujian telah difinalisasi! $savedCount nilai ulangan berhasil disimpan ke rekap nilai.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['Terjadi kesalahan saat finalisasi: ' . $e->getMessage()]);
        }
    }


    // ==================== HELPER ====================

    private function hitungNilaiPg(Ujian $ujian, HasilUjian $hasil, $soalPg = null, $totalBobotPg = null, $hasEssay = null, $jawabanMap = null, &$correctIds = null, &$incorrectIds = null)
    {
        $soalPg = $soalPg ?? $ujian->soals()->where('tipe', 'pg')->get();
        $totalBobotPg = $totalBobotPg ?? $soalPg->sum('bobot');

        if ($totalBobotPg == 0) {
            return ['nilai_pg' => 0];
        }

        $bobotBenar = 0;
        foreach ($soalPg as $soal) {
            if ($jawabanMap !== null) {
                $jawaban = $jawabanMap[$hasil->siswa_id . '_' . $soal->id] ?? null;
            } else {
                $jawaban = JawabanSiswa::where('siswa_id', $hasil->siswa_id)
                    ->where('soal_id', $soal->id)
                    ->first();
            }

            if ($jawaban && strtolower(trim($jawaban->jawaban)) === strtolower(trim($soal->jawaban_benar))) {
                if ($correctIds !== null) {
                    $correctIds[] = $jawaban->id;
                } else {
                    $jawaban->update(['is_correct' => true]);
                }
                $bobotBenar += $soal->bobot;
            } else if ($jawaban) {
                if ($incorrectIds !== null) {
                    $incorrectIds[] = $jawaban->id;
                } else {
                    $jawaban->update(['is_correct' => false]);
                }
            }
        }

        $nilaiPg = ($bobotBenar / $totalBobotPg) * 100;
        
        // Jika tidak ada essay, nilai_akhir = nilai_pg
        $hasEssay = $hasEssay ?? ($ujian->soalEssay()->count() > 0);
        $updateData = ['nilai_pg' => round($nilaiPg, 2)];
        
        if (!$hasEssay) {
            $updateData['nilai_akhir'] = round($nilaiPg, 2);
        }

        return $updateData;
    }

    // ==================== HASIL & KOREKSI UJIAN ====================

    public function indexHasil(Request $request)
    {
        $query = Ujian::with(['kelasList', 'hasilUjians'])
            ->withCount([
                'soals', 
                'hasilUjians',
                'soals as soal_essay_count' => function ($q) {
                    $q->where('tipe', 'essay');
                }
            ])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $ujians = $query->get();

        foreach ($ujians as $ujian) {
            $ujian->selesai_count = $ujian->hasilUjians->whereIn('status', ['selesai', 'dinilai'])->count();
            $ujian->perlu_koreksi = $ujian->soal_essay_count > 0 && $ujian->hasilUjians->where('status', 'selesai')->count() > 0;
        }

        return view('guru.ujian.hasil-index', compact('ujians'));
    }

    public function showHasil(Request $request, $id)
    {
        $ujian = Ujian::with(['kelasList', 'soals'])->findOrFail($id);
        
        $query = HasilUjian::where('ujian_id', $id)
            ->with(['siswa.user', 'siswa.kelas']);

        if ($request->filled('kelas_id')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        $hasilUjians = $query->orderByRaw("CASE WHEN status = 'dinilai' THEN 1 ELSE 0 END ASC")
            ->orderBy('created_at', 'desc')
            ->get();

        // Ambil daftar kelas yang ditargetkan oleh ujian ini (jika ada target khusus), atau semua kelas
        $kelasList = $ujian->kelasList->count() > 0 
            ? $ujian->kelasList 
            : Kelas::orderBy('nama_kelas', 'asc')->get();

        $selectedKelas = $request->input('kelas_id', '');
        $kkmSetting = Setting::where('key', 'kkm_nilai')->first();
        $kkm = $kkmSetting ? (float)$kkmSetting->value : 75;
        $hasEssay = $ujian->soalEssay()->count() > 0;

        return view('guru.ujian.hasil-show', compact('ujian', 'hasilUjians', 'kelasList', 'selectedKelas', 'kkm', 'hasEssay'));
    }

    public function detailJawabanSiswa($id, $siswaId)
    {
        $ujian = Ujian::with('soals')->findOrFail($id);
        $siswa = Siswa::with(['user', 'kelas'])->findOrFail($siswaId);
        
        $hasil = HasilUjian::where('ujian_id', $id)
            ->where('siswa_id', $siswaId)
            ->first();

        if (!$hasil) {
            return back()->withErrors(['Siswa belum mengerjakan ujian ini.']);
        }

        $jawaban = JawabanSiswa::where('ujian_id', $id)
            ->where('siswa_id', $siswaId)
            ->get()
            ->keyBy('soal_id');

        $logs = LogUjian::where('ujian_id', $id)
            ->where('siswa_id', $siswaId)
            ->orderBy('created_at', 'desc')
            ->get();

        $kkmSetting = Setting::where('key', 'kkm_nilai')->first();
        $kkm = $kkmSetting ? (float)$kkmSetting->value : 75;

        return view('guru.ujian.hasil-detail-siswa', compact('ujian', 'siswa', 'hasil', 'jawaban', 'logs', 'kkm'));
    }

    public function updateNilaiSiswaIndividu(Request $request, $id, $siswaId)
    {
        $ujian = Ujian::findOrFail($id);
        $hasil = HasilUjian::where('ujian_id', $id)->where('siswa_id', $siswaId)->firstOrFail();

        $request->validate([
            'nilai_pg' => 'required|numeric|min:0|max:100',
            'nilai_essay' => 'nullable|numeric|min:0|max:100',
            'nilai_akhir' => 'nullable|numeric|min:0|max:100',
            'essay_scores' => 'nullable|array',
            'essay_scores.*' => 'nullable|numeric|min:0|max:100',
        ]);

        $totalBobot = $ujian->totalBobot();
        $bobotPg = $ujian->soalPg()->sum('bobot');
        $bobotEssay = $ujian->soalEssay()->sum('bobot');

        $nilaiPg = $request->input('nilai_pg', $hasil->nilai_pg);
        $nilaiEssay = $request->input('nilai_essay', $hasil->nilai_essay);
        
        if ($request->filled('nilai_akhir')) {
            $nilaiAkhir = $request->input('nilai_akhir');
        } else {
            if ($totalBobot > 0 && $bobotEssay > 0) {
                $nilaiAkhir = (($nilaiPg * $bobotPg) + ($nilaiEssay * $bobotEssay)) / $totalBobot;
            } else {
                $nilaiAkhir = $nilaiPg;
            }
        }

        $hasil->update([
            'nilai_pg' => round($nilaiPg, 2),
            'nilai_essay' => round($nilaiEssay, 2),
            'nilai_akhir' => round($nilaiAkhir, 2),
            'status' => 'dinilai',
        ]);

        return back()->with('success', 'Nilai lembar ujian siswa berhasil diperbarui!');
    }
}
