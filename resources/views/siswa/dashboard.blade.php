@extends('layouts.app')
@section('title', 'Rapor Akademik')
@section('page_title', 'Rapor & Rekapitulasi')
@section('content')

@if(session('success'))
<div class="mb-4 bg-green-100 text-green-800 p-3 rounded text-sm border border-green-200">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="mb-4 bg-red-100 text-red-800 p-3 rounded text-sm border border-red-200">
    <ul class="list-disc ml-5">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
    <!-- Identitas Siswa -->
    <div class="lg:col-span-2 bg-white rounded border border-slate-200 p-4 shadow-sm flex items-center">
        <img src="{{ auth()->user()->avatar_url }}" class="w-16 h-16 rounded-full object-cover border-2 border-blue-500 mr-4 shadow-sm flex-shrink-0" alt="{{ auth()->user()->name }}">
        <div>
            <h2 class="text-xl font-bold text-slate-800">{{ auth()->user()->name }}</h2>
            <div class="text-sm text-slate-600 mt-1 flex gap-4">
                <span>Nomer Induk: <strong class="font-mono">{{ $siswa->nis ?? 'Tidak ditemukan' }}</strong></span>
                <span>Kelas: <strong>{{ $siswa->kelas->nama_kelas ?? 'Belum ada kelas' }}</strong></span>
            </div>
        </div>
    </div>

    <!-- Ringkasan Singkat -->
    <div class="bg-white rounded border border-slate-200 p-4 shadow-sm flex flex-col justify-center">
        @php
            $rataRata = $nilais->avg('nilai_akhir') ?? 0;
            // Variabel lainnya (rataKeaktifan, totalAbsen, hadir, sakit, izin, dispen, alpha, persentaseHadir)
            // sudah di-pass langsung dari Controller agar lebih efisien.
        @endphp
        <div class="flex justify-between items-center mb-2.5 pb-2 border-b border-slate-100">
            <div>
                <span class="block text-sm text-slate-600">Rata-rata Nilai Akhir:</span>
                <span class="text-[10px] font-bold {{ $rataRata >= $kkm ? 'text-green-600' : 'text-red-500' }}">
                    Status: {{ $rataRata >= $kkm ? 'TUNTAS' : 'BELUM TUNTAS' }} (KKM: {{ $kkm }})
                </span>
            </div>
            <span class="font-bold text-2xl {{ $rataRata >= $kkm ? 'text-green-600' : 'text-red-600' }}">{{ number_format($rataRata, 1) }}</span>
        </div>

        <div class="flex justify-between items-center mb-2.5 pb-2 border-b border-slate-100">
            <div>
                <span class="block text-sm text-slate-600">Rata-rata Keaktifan Harian:</span>
                <span class="text-[10px] font-bold text-blue-600">
                    Sikap & Partisipasi Kelas
                </span>
            </div>
            <span class="font-bold text-2xl text-blue-600">{{ number_format($rataKeaktifan, 1) }}</span>
        </div>

        <div class="flex justify-between items-center">
            <div>
                <span class="block text-sm text-slate-600">Kehadiran ({{ number_format($persentaseHadir, 0) }}%):</span>
                <span class="text-[10px] font-medium text-slate-500">
                    H: {{ $hadir }} | S: {{ $sakit }} | I: {{ $izin }} | D: {{ $dispen }} | A: {{ $alpha }}
                </span>
            </div>
            <span class="font-bold text-2xl {{ $persentaseHadir >= 80 ? 'text-blue-600' : 'text-red-600' }}">{{ $totalAbsen }}<span class="text-xs text-slate-400 font-normal">hari</span></span>
        </div>
    </div>
</div>

<div class="bg-white rounded border border-slate-200 shadow-sm overflow-hidden" x-data="{ tab: 'nilai' }">
    <!-- Tabs -->
    <div class="flex border-b border-slate-200 bg-slate-50 overflow-x-auto">
        <button @click="tab = 'nilai'" :class="tab === 'nilai' ? 'bg-white border-b-2 border-blue-600 text-blue-700 font-bold' : 'text-slate-600 hover:bg-slate-100'" class="px-5 py-3 text-sm transition-colors whitespace-nowrap">
            Daftar Nilai
        </button>
        <button @click="tab = 'materi'" :class="tab === 'materi' ? 'bg-white border-b-2 border-blue-600 text-blue-700 font-bold' : 'text-slate-600 hover:bg-slate-100'" class="px-5 py-3 text-sm transition-colors border-l border-slate-200 whitespace-nowrap">
            Materi Belajar
        </button>
        <button @click="tab = 'profil'" :class="tab === 'profil' ? 'bg-white border-b-2 border-blue-600 text-blue-700 font-bold' : 'text-slate-600 hover:bg-slate-100'" class="px-5 py-3 text-sm transition-colors border-l border-slate-200 whitespace-nowrap">
            Akun
        </button>
        <button @click="tab = 'testimoni'" :class="tab === 'testimoni' ? 'bg-white border-b-2 border-blue-600 text-blue-700 font-bold' : 'text-slate-600 hover:bg-slate-100'" class="px-5 py-3 text-sm transition-colors border-l border-slate-200 whitespace-nowrap">
            Testimoni
        </button>
    </div>

    <!-- Tab Nilai -->
    <div x-show="tab === 'nilai'" class="p-0">
        @if($nilais->isEmpty())
        <div class="p-8 text-center text-slate-500 italic">Belum ada nilai yang diinput oleh Guru.</div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse table-compact">
                <thead>
                    <tr>
                        <th class="w-12 text-center">No</th>
                        <th>Materi / Bab</th>
                        <th class="w-20 text-center bg-blue-50/50 text-blue-900 font-bold">Harian</th>
                        <th class="w-20 text-center">Tugas</th>
                        <th class="w-20 text-center">Quiz</th>
                        <th class="w-20 text-center">Proyek</th>
                        <th class="w-20 text-center bg-amber-50/60 text-amber-900 font-bold">Ulangan</th>
                        <th class="w-32 text-center bg-slate-100 font-bold">Nilai Akhir</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($nilais as $index => $nilai)
                    <tr class="hover:bg-slate-50">
                        <td class="text-center text-slate-500">{{ $index + 1 }}</td>
                        <td class="font-bold text-slate-700">{{ $nilai->bab }}</td>
                        <td class="text-center font-semibold text-blue-800 bg-blue-50/20">{{ number_format($rataKeaktifan, 1) }}</td>
                        <td class="text-center text-slate-600 font-medium">{{ (float)$nilai->tugas > 0 ? number_format($nilai->tugas, 1) : '-' }}</td>
                        <td class="text-center text-slate-600 font-medium">{{ (float)$nilai->quiz > 0 ? number_format($nilai->quiz, 1) : '-' }}</td>
                        <td class="text-center text-slate-600 font-medium">{{ (float)$nilai->proyek > 0 ? number_format($nilai->proyek, 1) : '-' }}</td>
                        <td class="text-center font-semibold text-amber-800 bg-amber-50/20">{{ (float)$nilai->ulangan > 0 ? number_format($nilai->ulangan, 1) : '-' }}</td>
                        <td class="text-center font-bold {{ $nilai->nilai_akhir >= $kkm ? 'text-green-700 bg-green-50' : 'text-red-700 bg-red-50' }} p-2">
                            <div class="flex items-center justify-center gap-1.5">
                                <span class="text-sm font-extrabold">{{ number_format($nilai->nilai_akhir, 1) }}</span>
                                @if($nilai->nilai_akhir >= $kkm)
                                    <span class="px-1.5 py-0.5 bg-green-200 text-green-800 text-[9px] font-bold rounded uppercase">Tuntas</span>
                                @else
                                    <span class="px-1.5 py-0.5 bg-red-200 text-red-800 text-[9px] font-bold rounded uppercase">Remedial</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <!-- Tab Materi Belajar -->
    <div x-show="tab === 'materi'" x-cloak class="p-4 sm:p-6 bg-slate-50">
        @if(isset($materis) && $materis->isEmpty())
        <div class="bg-white rounded border border-dashed border-slate-300 p-8 text-center shadow-sm">
            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            <p class="text-slate-500 font-medium">Belum ada materi belajar yang dibagikan guru.</p>
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
            @foreach($materis as $materi)
            <div class="bg-white rounded border border-slate-200 shadow-sm overflow-hidden flex flex-col group hover:shadow-md transition-shadow">
                @if($materi->foto)
                    <div class="h-32 sm:h-40 w-full overflow-hidden bg-slate-100">
                        <img src="{{ \App\Services\FileStorageService::url($materi->foto, 'materi') }}" alt="{{ $materi->judul }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    </div>
                @else
                    <div class="h-32 sm:h-40 w-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                        <svg class="w-12 h-12 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                    </div>
                @endif
                
                <div class="p-4 flex flex-col flex-grow">
                    <h4 class="font-bold text-slate-800 line-clamp-2 leading-tight mb-2">{{ $materi->judul }}</h4>
                    
                    <p class="text-xs text-slate-500 mb-4 flex-grow line-clamp-3">
                        {{ $materi->deskripsi ?: 'Tidak ada deskripsi.' }}
                    </p>
                    
                    <div class="mt-auto flex flex-col gap-2 pt-3 border-t border-slate-100">
                        <span class="text-[10px] font-medium text-slate-400 flex items-center mb-1">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $materi->created_at->diffForHumans() }}
                        </span>
                        
                        <div class="flex items-center justify-between">
                            @if($materi->file_materi)
                            <a href="{{ asset('uploads/materi/' . $materi->file_materi) }}" target="_blank" class="text-xs font-bold text-green-700 bg-green-100 hover:bg-green-200 px-3 py-1.5 rounded-full flex items-center transition-colors shadow-sm w-max">
                                <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                Unduh File
                            </a>
                            @else
                            <span></span>
                            @endif

                            @if($materi->link)
                            <a href="{{ $materi->link }}" target="_blank" class="text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-full flex items-center transition-colors">
                                Buka Link
                                <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- Tab Profil -->
    <div x-show="tab === 'profil'" x-cloak class="p-6">
        <h3 class="font-bold text-slate-700 mb-4 border-b border-slate-100 pb-2 text-sm">Pengaturan Akun & Profil Siswa</h3>
        
        <form action="{{ url('/app/profil') }}" method="POST" enctype="multipart/form-data" class="max-w-md space-y-4">
            @csrf
            @method('PUT')

            <!-- Upload Foto Profil -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2">Foto Profil Siswa</label>
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full border-2 border-blue-500 overflow-hidden bg-slate-100 flex items-center justify-center flex-shrink-0 shadow-sm">
                        <img src="{{ auth()->user()->avatar_url }}" class="w-full h-full object-cover" alt="{{ auth()->user()->name }}">
                    </div>
                    <div class="flex-1">
                        <input type="file" id="siswaAvatarInput" name="avatar" accept=".jpg,.jpeg,.png,.webp" onchange="compressSiswaAvatar(this)" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded p-1 bg-slate-50">
                        <p class="text-[10px] text-slate-400 mt-1">Format: JPG, JPEG, PNG (Foto otomatis dioptimasi)</p>
                    </div>
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Username (Untuk Login)</label>
                <input type="text" name="username" value="{{ auth()->user()->username }}" required class="input-compact w-full bg-slate-50">
                <p class="text-[10px] text-slate-400 mt-1">Defaultnya adalah Nomer Induk Anda.</p>
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Password Baru (Opsional)</label>
                <input type="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah" class="input-compact w-full bg-slate-50">
                <p class="text-[10px] text-slate-400 mt-1">Isi minimal 4 karakter jika ingin mengganti sandi.</p>
            </div>
            
            <div class="pt-2">
                <button type="submit" class="btn-compact bg-blue-600 hover:bg-blue-700 text-white shadow-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>

    <!-- Tab Testimoni -->
    <div x-show="tab === 'testimoni'" x-cloak class="p-6">
        <h3 class="font-bold text-slate-700 mb-4 border-b border-slate-100 pb-2 text-sm">Kirim Testimoni</h3>
        
        <form action="{{ url('/app/komentar') }}" method="POST" class="max-w-xl space-y-4">
            @csrf
            
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Isi Komentar</label>
                <textarea name="isi_komentar" required maxlength="300" rows="4" class="input-compact w-full bg-slate-50" placeholder="Tuliskan pengalaman atau masukan Anda di sini..."></textarea>
                <p class="text-[10px] text-slate-400 mt-1">Maksimal 300 karakter. Komentar hanya bisa dikirim 1x setiap 7 hari.</p>
            </div>
            
            <div class="flex items-center mt-2">
                <input type="checkbox" name="is_anonim" id="is_anonim" value="1" class="w-4 h-4 text-blue-600 bg-slate-100 border-slate-300 rounded focus:ring-blue-500">
                <label for="is_anonim" class="ml-2 text-sm font-medium text-slate-700">Tampilkan sebagai Anonim (sembunyikan nama saya)</label>
            </div>
            
            <div class="pt-2">
                <button type="submit" class="btn-compact bg-blue-600 hover:bg-blue-700 text-white shadow-sm">Kirim Komentar</button>
            </div>
        </form>
    </div>
</div>

<script>
let compressedSiswaAvatarBlob = null;

function compressSiswaAvatar(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    compressedSiswaAvatarBlob = null;

    // Micro-Square Compression: 150x150px (hanya ~10KB - 15KB)
    const reader = new FileReader();
    reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
            try {
                const canvas = document.createElement('canvas');
                const targetSize = 150;
                canvas.width = targetSize;
                canvas.height = targetSize;
                const ctx = canvas.getContext('2d');

                let srcX = 0, srcY = 0, srcSize = Math.min(img.width, img.height);
                if (img.width > img.height) {
                    srcX = (img.width - img.height) / 2;
                } else {
                    srcY = (img.height - img.width) / 2;
                }

                ctx.drawImage(img, srcX, srcY, srcSize, srcSize, 0, 0, targetSize, targetSize);

                canvas.toBlob(function(blob) {
                    if (blob && blob.size > 0) {
                        compressedSiswaAvatarBlob = blob;
                    }
                }, 'image/jpeg', 0.85);
            } catch (err) {
                compressedSiswaAvatarBlob = null;
            }
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

document.addEventListener('DOMContentLoaded', function() {
    const profileForm = document.querySelector('form[action="{{ url('/app/profil') }}"]');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            if (compressedSiswaAvatarBlob) {
                e.preventDefault();
                const formData = new FormData(profileForm);
                formData.set('avatar', compressedSiswaAvatarBlob, 'avatar.jpg');

                const submitBtn = profileForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerText = 'Menyimpan...';
                }

                fetch('{{ url("/app/profil") }}', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData,
                    redirect: 'manual'
                })
                .then(response => {
                    if (response.status === 200 || response.type === 'opaqueredirect' || response.status === 0) {
                        window.location.reload();
                    } else {
                        alert('Gagal menyimpan profil. Silakan coba lagi.');
                        if (submitBtn) { submitBtn.disabled = false; submitBtn.innerText = 'Simpan Perubahan'; }
                    }
                })
                .catch(() => {
                    alert('Koneksi terputus. Silakan coba lagi.');
                    if (submitBtn) { submitBtn.disabled = false; submitBtn.innerText = 'Simpan Perubahan'; }
                });
            }
        });
    }
});
</script>

@endsection
