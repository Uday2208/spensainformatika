<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Komentar;
use App\Models\Materi;
use Carbon\Carbon;

class SiswaController extends Controller
{
    public function dashboard()
    {
        // Load the student data along with their attendance and grades
        $siswa = Auth::user()->siswa()->with(['kelas'])->first();
        
        $absensis = collect([]);
        $nilais = collect([]);
        $penilaianHarians = collect([]);
        $rekapAbsensiMingguan = [];
        
        if ($siswa) {
            $nilais = $siswa->nilais()->get();

            // SQL Agregasi Absensi
            $agregasiAbsensi = $siswa->absensis()
                ->selectRaw('count(*) as total,
                             sum(case when status = "hadir" then 1 else 0 end) as hadir,
                             sum(case when status = "sakit" then 1 else 0 end) as sakit,
                             sum(case when status = "izin" then 1 else 0 end) as izin,
                             sum(case when status = "dispen" then 1 else 0 end) as dispen,
                             sum(case when status = "alpha" then 1 else 0 end) as alpha')
                ->first();
                
            $totalAbsen = (int)($agregasiAbsensi->total ?? 0);
            $hadir = (int)($agregasiAbsensi->hadir ?? 0);
            $sakit = (int)($agregasiAbsensi->sakit ?? 0);
            $izin = (int)($agregasiAbsensi->izin ?? 0);
            $dispen = (int)($agregasiAbsensi->dispen ?? 0);
            $alpha = (int)($agregasiAbsensi->alpha ?? 0);

            // SQL Agregasi Penilaian Harian
            $agregasiPH = $siswa->penilaianHarians()
                ->selectRaw('count(*) as total, avg(nilai) as rata')
                ->first();
            $rataKeaktifan = ($agregasiPH && $agregasiPH->total > 0) ? (float)$agregasiPH->rata : 80;
        } else {
            $totalAbsen = $hadir = $sakit = $izin = $dispen = $alpha = 0;
            $rataKeaktifan = 80;
        }

        $persentaseHadir = $totalAbsen > 0 ? (($hadir + $dispen) / $totalAbsen) * 100 : 0;

        $kkmSetting = \App\Models\Setting::where('key', 'kkm_nilai')->first();
        $kkm = $kkmSetting ? $kkmSetting->value : 75;

        // Menggunakan select() spesifik kolom yang dibutuhkan Blade dan get() biasa untuk menghindari bug cursor()
        $materis = Materi::select('id', 'judul', 'deskripsi', 'foto', 'file_materi', 'link', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('siswa.dashboard', compact(
            'siswa', 'nilais', 'kkm', 'materis',
            'totalAbsen', 'hadir', 'sakit', 'izin', 'dispen', 'alpha',
            'persentaseHadir', 'rataKeaktifan'
        ));
    }

    public function updateProfil(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'username' => 'required|string|unique:users,username,' . $user->id,
            'password' => 'nullable|string|min:4',
            'avatar'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        $dataToUpdate = [
            'username' => $request->username,
        ];

        if ($request->filled('password')) {
            $dataToUpdate['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('avatar')) {
            // Delete old avatar file if not a base64 string
            if ($user->avatar && !str_starts_with($user->avatar, 'data:image')) {
                \App\Services\FileStorageService::delete($user->avatar, 'avatars');
            }

            $dataToUpdate['avatar'] = \App\Services\FileStorageService::upload($request->file('avatar'), 'avatars');
        }

        $user->update($dataToUpdate);

        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    public function storeKomentar(Request $request)
    {
        $request->validate([
            'isi_komentar' => 'required|string|max:300',
        ]);

        $siswa = Auth::user()->siswa;
        if (!$siswa) {
            return back()->withErrors(['Akses ditolak.']);
        }

        $lastKomentar = Komentar::where('siswa_id', $siswa->id)
            ->latest('created_at')
            ->first();

        if ($lastKomentar) {
            $daysDiff = Carbon::parse($lastKomentar->created_at)->diffInDays(Carbon::now());
            if ($daysDiff < 7) {
                return back()->withErrors(['Anda hanya dapat mengirim komentar 1 kali dalam 7 hari.']);
            }
        }

        Komentar::create([
            'siswa_id' => $siswa->id,
            'isi_komentar' => $request->isi_komentar,
            'is_anonim' => $request->has('is_anonim') ? true : false,
        ]);

        return back()->with('success', 'Komentar berhasil dikirim dan akan tampil di halaman utama!');
    }

    /**
     * Halaman Kehadiran Saya untuk Siswa (Read-Only)
     */
    public function kehadiranSaya(Request $request)
    {
        $siswa = Auth::user()->siswa()->with(['kelas'])->first();

        if (!$siswa) {
            return redirect('/app/dashboard-siswa')->withErrors(['Data siswa tidak ditemukan.']);
        }

        // Single optimized aggregation query to get rekap counts
        $rekapQuery = DB::table('absensis')
            ->where('siswa_id', $siswa->id)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Standardize status counts (lowercase keys)
        $rekapLower = [];
        foreach ($rekapQuery as $stKey => $cnt) {
            $rekapLower[strtolower(trim($stKey))] = (int)$cnt;
        }

        $rekap = [
            'hadir'  => $rekapLower['hadir'] ?? 0,
            'sakit'  => $rekapLower['sakit'] ?? 0,
            'izin'   => $rekapLower['izin'] ?? 0,
            'alpa'   => $rekapLower['alpha'] ?? ($rekapLower['alpa'] ?? 0),
            'dispen' => $rekapLower['dispen'] ?? 0,
            'total'  => array_sum($rekapLower),
        ];

        // Paginated attendance list sorted by date descending (latest first)
        $absensis = $siswa->absensis()
            ->orderBy('tanggal', 'desc')
            ->paginate(15);

        // Fetch meeting & notes mapping from penilaian_harians or absensis
        $phMap = DB::table('penilaian_harians')
            ->where('siswa_id', $siswa->id)
            ->get()
            ->keyBy(fn($item) => \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d'));

        return view('siswa.kehadiran', compact('siswa', 'rekap', 'absensis', 'phMap'));
    }
}
