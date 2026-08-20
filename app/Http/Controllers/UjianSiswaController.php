<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Ujian;
use App\Models\Soal;
use App\Models\HasilUjian;
use App\Models\JawabanSiswa;
use App\Models\LogUjian;

class UjianSiswaController extends Controller
{
    /**
     * Daftar ujian aktif sesuai kelas siswa
     */
    public function index()
    {
        $siswa = Auth::user()->siswa()->with('kelas')->first();

        if (!$siswa) {
            return redirect('/app/dashboard-siswa')->withErrors(['Data siswa tidak ditemukan.']);
        }

        // Ujian aktif untuk kelas siswa ini
        $ujianAktif = Ujian::whereHas('kelasList', fn($q) => $q->where('kelas.id', $siswa->kelas_id))
            ->where('status', 'aktif')
            ->with('kelasList')
            ->first();

        // Ujian yang sudah dikerjakan siswa (selesai/dinilai)
        $riwayat = HasilUjian::where('siswa_id', $siswa->id)
            ->with(['ujian' => fn($q) => $q->select('id', 'judul', 'bab', 'tanggal', 'status')])
            ->whereIn('status', ['selesai', 'dinilai'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Cek apakah siswa sedang mengerjakan ujian
        $sedangMengerjakan = HasilUjian::where('siswa_id', $siswa->id)
            ->where('status', 'mengerjakan')
            ->with('ujian')
            ->first();

        return view('siswa.ujian.index', compact('siswa', 'ujianAktif', 'riwayat', 'sedangMengerjakan'));
    }

    /**
     * Validasi token dan mulai ujian
     */
    public function masuk(Request $request, $id)
    {
        $request->validate([
            'token' => 'required|string|size:6',
        ]);

        $siswa = Auth::user()->siswa()->with('kelas')->first();
        if (!$siswa) {
            return back()->withErrors(['Data siswa tidak ditemukan.']);
        }

        $ujian = Ujian::with('kelasList')->find($id);

        if (!$ujian || !$ujian->isAktif()) {
            return back()->withErrors(['Ujian tidak aktif atau tidak ditemukan.']);
        }

        // Cek kelas siswa sesuai
        $kelasIds = $ujian->kelasList->pluck('id')->toArray();
        if (!in_array($siswa->kelas_id, $kelasIds)) {
            return back()->withErrors(['Anda tidak terdaftar di kelas ujian ini.']);
        }

        // Cek token
        if (strtoupper(trim($request->token)) !== strtoupper($ujian->token)) {
            return back()->withErrors(['Token ujian salah.']);
        }

        // Cek sudah pernah submit
        $existing = HasilUjian::where('siswa_id', $siswa->id)
            ->where('ujian_id', $ujian->id)
            ->first();

        if ($existing && in_array($existing->status, ['selesai', 'dinilai'])) {
            return redirect()->route('siswa.ujian.hasil', $ujian->id)
                ->with('info', 'Anda sudah menyelesaikan ujian ini.');
        }

        // Cek multi device — tandai session
        $deviceKey = 'ujian_device_' . $ujian->id;
        $existingSession = session($deviceKey);

        if ($existing && $existing->status === 'mengerjakan') {
            // Resume – perbarui session
            session([$deviceKey => $siswa->id]);
            return redirect()->route('siswa.ujian.kerjakan', $ujian->id);
        }

        // Buat record hasil ujian baru
        DB::beginTransaction();
        try {
            HasilUjian::create([
                'siswa_id'    => $siswa->id,
                'ujian_id'    => $ujian->id,
                'status'      => 'mengerjakan',
                'started_at'  => now(),
            ]);

            LogUjian::create([
                'siswa_id'   => $siswa->id,
                'ujian_id'   => $ujian->id,
                'event'      => 'mulai',
                'detail'     => 'Siswa mulai mengerjakan ujian',
                'created_at' => now(),
            ]);

            DB::commit();
            session([$deviceKey => $siswa->id]);
            return redirect()->route('siswa.ujian.kerjakan', $ujian->id);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['Gagal memulai ujian. Silakan coba lagi.']);
        }
    }

    /**
     * Halaman pengerjaan ujian
     */
    public function kerjakan($id)
    {
        $siswa = Auth::user()->siswa;
        if (!$siswa) {
            return redirect()->route('siswa.ujian.index');
        }

        $ujian = Ujian::with(['soals' => fn($q) => $q->orderBy('urutan')])->find($id);

        if (!$ujian || !$ujian->isAktif()) {
            return redirect()->route('siswa.ujian.index')->withErrors(['Ujian sudah tidak aktif.']);
        }

        $hasil = HasilUjian::where('siswa_id', $siswa->id)
            ->where('ujian_id', $id)
            ->first();

        if (!$hasil) {
            return redirect()->route('siswa.ujian.index')->withErrors(['Anda belum memulai ujian ini.']);
        }

        if (in_array($hasil->status, ['selesai', 'dinilai'])) {
            return redirect()->route('siswa.ujian.hasil', $id);
        }

        // Ambil jawaban yang sudah disimpan
        $jawabanTersimpan = JawabanSiswa::where('siswa_id', $siswa->id)
            ->where('ujian_id', $id)
            ->pluck('jawaban', 'soal_id');

        // Hitung sisa waktu berdasarkan server time
        $startedAt = $hasil->started_at;
        $durasiDetik = $ujian->durasi * 60;
        $terpakai = now()->diffInSeconds($startedAt);
        return response()
            ->view('siswa.ujian.kerjakan', compact('ujian', 'hasil', 'jawabanTersimpan', 'sisaDetik'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');
    }

    /**
     * AJAX: simpan jawaban (auto-save)
     */
    public function simpanJawaban(Request $request, $id)
    {
        if (!$request->ajax() && !$request->wantsJson() && !$request->isJson() && $request->header('X-Requested-With') !== 'XMLHttpRequest') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $siswa = Auth::user()->siswa;
        if (!$siswa) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $jawabanPayload = $request->input('jawaban', []);
        
        if (!is_array($jawabanPayload) || empty($jawabanPayload)) {
            return response()->json(['success' => true]);
        }

        DB::beginTransaction();
        try {
            $hasil = HasilUjian::where('siswa_id', $siswa->id)
                ->where('ujian_id', $id)
                ->lockForUpdate()
                ->first();

            if (!$hasil) {
                DB::rollBack();
                return response()->json(['error' => 'Ujian tidak ditemukan'], 400);
            }

            if ($hasil->status !== 'mengerjakan') {
                DB::rollBack();
                return response()->json(['error' => 'Ujian sudah selesai'], 409);
            }

            $soalIds = Soal::where('ujian_id', $id)->pluck('id')->toArray();
            
            $now = now();
            $batchJawaban = [];

            foreach ($jawabanPayload as $soalId => $jawaban) {
                if (in_array($soalId, $soalIds)) {
                    $batchJawaban[] = [
                        'siswa_id'   => $siswa->id,
                        'soal_id'    => $soalId,
                        'ujian_id'   => $id,
                        'jawaban'    => $jawaban,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if (!empty($batchJawaban)) {
                JawabanSiswa::upsert(
                    $batchJawaban,
                    ['siswa_id', 'soal_id'], // Unique index
                    ['ujian_id', 'jawaban', 'updated_at']
                );
            }

            DB::commit();
            return response()->json(['success' => true, 'saved_at' => now()->toTimeString()]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error simpan jawaban autosave: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal menyimpan jawaban'], 500);
        }
    }

    /**
     * Submit ujian (selesai mengerjakan)
     */
    public function submit(Request $request, $id)
    {
        $siswa = Auth::user()->siswa;
        if (!$siswa) {
            return $this->submitResponse($request, false, 'Unauthorized');
        }

        $ujian = Ujian::with('soals')->find($id);
        if (!$ujian) {
            return $this->submitResponse($request, false, 'Ujian tidak ditemukan.');
        }

        DB::beginTransaction();
        try {
            $hasil = HasilUjian::where('siswa_id', $siswa->id)
                ->where('ujian_id', $id)
                ->lockForUpdate()
                ->first();

            if (!$hasil) {
                DB::rollBack();
                return $this->submitResponse($request, false, 'Tidak ada sesi ujian aktif.');
            }

            if ($hasil->status !== 'mengerjakan') {
                DB::rollBack();
                return $this->submitResponse($request, true, 'Ujian sudah dikumpulkan sebelumnya.', $id);
            }

            // Simpan seluruh jawaban dari payload (termasuk yang gagal auto-save) secara batch
            $jawabanPayload = $request->input('jawaban', []);
            if (is_array($jawabanPayload) && !empty($jawabanPayload)) {
                $soalIds = $ujian->soals->pluck('id')->toArray();
                $now = now();
                $batchSubmitJawaban = [];

                foreach ($jawabanPayload as $soalId => $jawaban) {
                    if (in_array($soalId, $soalIds)) {
                        $batchSubmitJawaban[] = [
                            'siswa_id'   => $siswa->id,
                            'soal_id'    => $soalId,
                            'ujian_id'   => $id,
                            'jawaban'    => $jawaban,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                if (!empty($batchSubmitJawaban)) {
                    JawabanSiswa::upsert(
                        $batchSubmitJawaban,
                        ['siswa_id', 'soal_id'],
                        ['ujian_id', 'jawaban', 'updated_at']
                    );
                }
            }

            // Hitung nilai PG
            $soalPg = $ujian->soals->where('tipe', 'pg');
            $totalBobotPg = $soalPg->sum('bobot');
            $bobotBenar = 0;

            // Pre-fetch jawaban siswa untuk menghindari N+1 query
            $jawabanSiswa = JawabanSiswa::where('siswa_id', $siswa->id)
                ->where('ujian_id', $id)
                ->whereIn('soal_id', $soalPg->pluck('id'))
                ->get()
                ->keyBy('soal_id');

            $correctIds = [];
            $incorrectIds = [];

            foreach ($soalPg as $soal) {
                $jawaban = $jawabanSiswa->get($soal->id);

                if ($jawaban && strtolower(trim($jawaban->jawaban)) === strtolower(trim($soal->jawaban_benar))) {
                    $correctIds[] = $jawaban->id;
                    $bobotBenar += $soal->bobot;
                } elseif ($jawaban) {
                    $incorrectIds[] = $jawaban->id;
                }
            }

            // Bulk update jawaban
            if (!empty($correctIds)) {
                JawabanSiswa::whereIn('id', $correctIds)->update(['is_correct' => true, 'updated_at' => now()]);
            }
            if (!empty($incorrectIds)) {
                JawabanSiswa::whereIn('id', $incorrectIds)->update(['is_correct' => false, 'updated_at' => now()]);
            }

            $nilaiPg = $totalBobotPg > 0 ? round(($bobotBenar / $totalBobotPg) * 100, 2) : 0;

            // Jika tidak ada essay, nilai_akhir = nilai_pg
            $hasEssay = $ujian->soals->where('tipe', 'essay')->count() > 0;
            $updateData = [
                'nilai_pg'    => $nilaiPg,
                'status'      => 'selesai',
                'finished_at' => now(),
            ];

            if (!$hasEssay) {
                $updateData['nilai_akhir'] = $nilaiPg;
            }

            $hasil->update($updateData);

            // Hapus session device
            session()->forget('ujian_device_' . $id);

            LogUjian::create([
                'siswa_id'   => $siswa->id,
                'ujian_id'   => $id,
                'event'      => 'submit',
                'detail'     => $request->input('reason', 'Siswa submit manual'),
                'created_at' => now(),
            ]);

            DB::commit();
            return $this->submitResponse($request, true, 'Ujian berhasil dikumpulkan!', $id);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->submitResponse($request, false, 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    private function submitResponse(Request $request, bool $success, string $message, $ujianId = null)
    {
        if ($request->ajax() || $request->wantsJson() || $request->isJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success'    => $success,
                'message'    => $message,
                'redirect'   => $ujianId ? route('siswa.ujian.hasil', $ujianId) : route('siswa.ujian.index'),
            ]);
        }

        if ($success && $ujianId) {
            return redirect()->route('siswa.ujian.hasil', $ujianId)->with('success', $message);
        }

        return redirect()->route('siswa.ujian.index')->withErrors([$message]);
    }

    /**
     * Halaman hasil ujian siswa (hanya skor akhir, tidak ada detail jawaban)
     */
    public function hasil($id)
    {
        $siswa = Auth::user()->siswa;
        if (!$siswa) {
            return redirect()->route('siswa.ujian.index');
        }

        $ujian = Ujian::select('id', 'judul', 'bab', 'tanggal', 'status')->find($id);
        if (!$ujian) {
            return redirect()->route('siswa.ujian.index');
        }

        $hasil = HasilUjian::where('siswa_id', $siswa->id)
            ->where('ujian_id', $id)
            ->first();

        if (!$hasil) {
            return redirect()->route('siswa.ujian.index');
        }

        // Ambil KKM dari settings
        $kkm = \App\Models\Setting::where('key', 'kkm_nilai')->value('value') ?? 75;

        return view('siswa.ujian.hasil', compact('ujian', 'hasil', 'kkm'));
    }

    /**
     * AJAX: log event kecurangan
     */
    public function logKecurangan(Request $request)
    {
        if (!$request->ajax() && !$request->wantsJson() && !$request->isJson() && $request->header('X-Requested-With') !== 'XMLHttpRequest') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $siswa = Auth::user()->siswa;
        if (!$siswa) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $ujianId = $request->input('ujian_id');
        $event   = $request->input('event');

        if (!$ujianId || !$event) {
            return response()->json(['error' => 'Data tidak lengkap'], 400);
        }

        // Verifikasi siswa sedang mengerjakan ujian ini
        $hasil = HasilUjian::where('siswa_id', $siswa->id)
            ->where('ujian_id', $ujianId)
            ->where('status', 'mengerjakan')
            ->first();

        if (!$hasil) {
            return response()->json(['error' => 'Tidak ada sesi aktif'], 400);
        }

        LogUjian::create([
            'siswa_id'   => $siswa->id,
            'ujian_id'   => $ujianId,
            'event'      => $event,
            'detail'     => $request->input('detail', ''),
            'created_at' => now(),
        ]);

        // Update tab_switch_count
        if (in_array($event, ['tab_switch', 'minimize', 'blur'])) {
            $hasil->increment('tab_switch_count');
            $switchCount = $hasil->fresh()->tab_switch_count;
            return response()->json(['success' => true, 'switch_count' => $switchCount]);
        }

        return response()->json(['success' => true]);
    }
}
