@extends('layouts.app')
@section('title', 'Akun & Profil Saya')
@section('page_title', '👤 Pengaturan Akun')
@section('content')

@if(session('success'))
<div class="mb-4 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl shadow-xs">
    <div class="w-7 h-7 bg-emerald-500 rounded-full flex items-center justify-center flex-shrink-0 text-white font-bold">✓</div>
    <p class="font-semibold text-sm">{{ session('success') }}</p>
</div>
@endif

@if($errors->any())
<div class="mb-4 flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 p-4 rounded-2xl shadow-xs">
    <div class="w-7 h-7 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0 text-white font-bold mt-0.5">✕</div>
    <div class="flex-1">
        <strong class="block text-sm font-bold">Gagal Menyimpan:</strong>
        <ul class="list-disc ml-5 text-xs text-red-700 mt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

<div class="max-w-2xl bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8">
    <div class="flex items-center gap-3 pb-4 mb-6 border-b border-slate-100">
        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl font-bold">
            ⚙️
        </div>
        <div>
            <h2 class="text-base sm:text-lg font-black text-slate-800">Pengaturan Akun & Profil</h2>
            <p class="text-xs text-slate-500">Perbarui foto profil, username login, atau ubah kata sandi akun Anda.</p>
        </div>
    </div>

    <form action="{{ url('/app/profil') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @method('PUT')

        <!-- Identitas Sekolah (Read-Only) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50/80 p-4 rounded-2xl border border-slate-200/80">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Nama Lengkap</span>
                <span class="text-sm font-black text-slate-800 block mt-0.5">{{ $user->name }}</span>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Kelas & NIS</span>
                <span class="text-sm font-bold text-slate-700 block mt-0.5">
                    Kelas {{ $siswa->kelas->nama_kelas ?? '-' }} | NIS: {{ $siswa->nis ?? '-' }}
                </span>
            </div>
        </div>

        <!-- Upload Foto Profil -->
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Foto Profil Siswa</label>
            <div class="flex items-center gap-4">
                <div class="w-20 h-20 rounded-2xl border-2 border-blue-500 overflow-hidden bg-slate-100 flex items-center justify-center flex-shrink-0 shadow-xs">
                    <img src="{{ $user->avatar_url }}" class="w-full h-full object-cover" alt="{{ $user->name }}">
                </div>
                <div class="flex-1">
                    <input type="file" id="siswaAvatarInput" name="avatar" accept=".jpg,.jpeg,.png,.webp" onchange="compressSiswaAvatar(this)" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-2xl p-1 bg-slate-50 cursor-pointer">
                    <p class="text-[11px] text-slate-400 mt-1.5">Format: JPG, PNG, WEBP (Foto otomatis dioptimasi agar hemat kuota).</p>
                </div>
            </div>
        </div>
        
        <!-- Username Login -->
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Username Login</label>
            <input type="text" name="username" value="{{ $user->username }}" required placeholder="buat username tanpa spasi" class="input-compact w-full bg-slate-50 rounded-2xl px-4 py-3 min-h-[44px] font-bold text-slate-800 text-sm border-slate-200 focus:bg-white">
            <p class="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                <span>💡</span>
                <span>Username standar adalah NIS. <strong>Buat username tanpa spasi</strong>.</span>
            </p>
        </div>
        
        <!-- Password Baru -->
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Password Baru (Opsional)</label>
            <input type="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah sandi saat ini" class="input-compact w-full bg-slate-50 rounded-2xl px-4 py-3 min-h-[44px] text-sm border-slate-200 focus:bg-white">
            <p class="text-[11px] text-slate-400 mt-1">Isi minimal 4 karakter hanya jika ingin mengganti kata sandi login.</p>
        </div>
        
        <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
            <a href="{{ route('siswa.dashboard') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all">
                Kembali ke Dashboard
            </a>
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-xl shadow-md active:scale-95 transition-all">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<script>
let compressedSiswaAvatarBlob = null;

function compressSiswaAvatar(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    compressedSiswaAvatarBlob = null;

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
