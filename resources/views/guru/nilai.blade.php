@extends('layouts.app')
@section('title', 'Input Nilai')
@section('page_title', 'Input Nilai Siswa')
@section('content')

@if(session('success'))
<div class="mb-4 bg-green-100 text-green-800 p-3 rounded text-sm border border-green-200">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="mb-4 bg-red-100 text-red-800 p-3.5 rounded text-sm border border-red-200 shadow-sm">
    <div class="font-bold mb-1 flex items-center gap-1.5">
        <svg class="w-4 h-4 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        Pemberitahuan:
    </div>
    <ul class="list-disc list-inside space-y-0.5 text-xs text-red-700">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div x-data="{ 
    tab: 'input', 
    pHarian: 20, 
    pTugas: 20, 
    pQuiz: 20, 
    pProyek: 20, 
    pUlangan: 20, 
    useHarian: true, 
    useTugas: true, 
    useQuiz: true, 
    useProyek: true, 
    editModalOpen: false, 
    kkmModalOpen: false, 
    editData: { id: '', siswa_id: '', siswa_nama: '', bab: '', harian: 80, tugas: 0, quiz: 0, proyek: 0, ulangan: 0 }, 
    sertakanUlangan: false, 
    rataUjian: {}, 
    isSaving: false,
    totalBobotAktif() {
        return (this.useHarian ? (parseFloat(this.pHarian) || 0) : 0)
             + (this.useTugas ? (parseFloat(this.pTugas) || 0) : 0)
             + (this.useQuiz ? (parseFloat(this.pQuiz) || 0) : 0)
             + (this.useProyek ? (parseFloat(this.pProyek) || 0) : 0)
             + (this.sertakanUlangan ? (parseFloat(this.pUlangan) || 0) : 0);
    },
    hitungNilai(harian, tugas, quiz, proyek, ulangan) {
        const tot = this.totalBobotAktif();
        if (tot <= 0) return '0.0';
        const skor = (this.useHarian ? (parseFloat(harian || 0) * (parseFloat(this.pHarian) || 0)) : 0)
                   + (this.useTugas ? (parseFloat(tugas || 0) * (parseFloat(this.pTugas) || 0)) : 0)
                   + (this.useQuiz ? (parseFloat(quiz || 0) * (parseFloat(this.pQuiz) || 0)) : 0)
                   + (this.useProyek ? (parseFloat(proyek || 0) * (parseFloat(this.pProyek) || 0)) : 0)
                   + (this.sertakanUlangan ? (parseFloat(ulangan || 0) * (parseFloat(this.pUlangan) || 0)) : 0);
        return (skor / tot).toFixed(1);
    }
}">
    
    <!-- Tabs -->
    <div class="flex flex-col sm:flex-row justify-between mb-4 border-b border-slate-200">
        <div class="flex overflow-x-auto">
            <button @click="tab = 'input'" :class="tab === 'input' ? 'bg-white border-t-2 border-l border-r border-blue-600 text-blue-700 font-bold shadow-sm' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border-transparent'" class="px-5 py-3 text-sm transition-colors border-t-2 rounded-t mr-1 whitespace-nowrap">
                Input Nilai Baru
            </button>
            <button @click="tab = 'riwayat'" :class="tab === 'riwayat' ? 'bg-white border-t-2 border-l border-r border-blue-600 text-blue-700 font-bold shadow-sm' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border-transparent'" class="px-5 py-3 text-sm transition-colors border-t-2 rounded-t mr-1 whitespace-nowrap">
                Riwayat & Edit Nilai
            </button>
            <button @click="tab = 'rekap'" :class="tab === 'rekap' ? 'bg-white border-t-2 border-l border-r border-blue-600 text-blue-700 font-bold shadow-sm' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border-transparent'" class="px-5 py-3 text-sm transition-colors border-t-2 rounded-t whitespace-nowrap">
                Rekap Semester
            </button>
        </div>
        <div class="py-2 sm:py-0 flex items-center">
            <button @click="kkmModalOpen = true" class="btn-compact bg-amber-100 text-amber-700 hover:bg-amber-200 border border-amber-300 shadow-sm flex items-center text-xs">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                Atur KKM ({{ $kkm }})
            </button>
        </div>
    </div>

    <!-- Tab Input -->
    <div x-show="tab === 'input'" class="bg-white rounded-b rounded-tr border border-slate-200 shadow-sm p-4">
    <form action="{{ url('/app/nilai') }}" method="POST" id="nilaiForm" @submit="isSaving = true">
        @csrf
        <input type="hidden" name="force_update" id="inputForceUpdate" value="0">
        
        <!-- Controls -->
        <div class="flex flex-col lg:flex-row lg:items-end justify-between mb-4 pb-4 border-b border-slate-100 gap-4">
            <div class="flex flex-col md:flex-row items-start md:items-end space-y-3 md:space-y-0 md:space-x-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Materi / Bab</label>
                    <div class="flex items-center space-x-2">
                        <input type="text" name="bab" id="inputBabUtama" list="daftarBabList" required placeholder="Contoh: Bab 1 - Aljabar" class="input-compact bg-slate-50 w-full md:w-48" onchange="enableSertakanUlanganAndFetch(); checkBabExistsWarning();" oninput="clearTimeout(window._babDebounce); checkBabExistsWarning(); if(this.value.trim() !== '') { window._babDebounce = setTimeout(function(){ enableSertakanUlanganAndFetch(); }, 700); }">
                        <button type="button" onclick="loadExistingGrades()" class="btn-compact bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs whitespace-nowrap" title="Muat nilai sebelumnya jika bab sudah ada">Muat Data</button>
                    </div>
                    <div id="warningBabSudahAda" class="hidden"></div>
                    <datalist id="daftarBabList">
                        @foreach($daftar_bab as $bab)
                            <option value="{{ $bab }}">
                        @endforeach
                    </datalist>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Pilih Kelas</label>
                    <select name="kelas_id" onchange="if(this.value){ window.location.href='{{ url()->current() }}?kelas_id=' + this.value; } else { window.location.href='{{ url()->current() }}'; }" class="input-compact bg-slate-50 w-full md:w-32 cursor-pointer">
                        <option value="">-- Pilih --</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Bobot Persentase dengan Ceklis Dinamis -->
                <div class="flex space-x-2 bg-blue-50 p-1.5 rounded border border-blue-100">
                    <div class="flex flex-col items-center">
                        <label class="flex items-center gap-1 cursor-pointer mb-1" title="Centang untuk mengaktifkan Penilaian Harian">
                            <input type="checkbox" x-model="useHarian" @change="if(!useHarian) { pHarian = 0; } else if(pHarian <= 0) { pHarian = 20; }" class="w-3 h-3 text-blue-600 rounded focus:ring-blue-500 cursor-pointer">
                            <span class="text-[10px] font-bold text-blue-800">Harian (%)</span>
                        </label>
                        <input type="number" name="p_harian" x-model.number="pHarian" min="0" max="100" x-show="useHarian" :disabled="!useHarian" class="input-compact w-16 text-center text-xs">
                        <input type="hidden" name="p_harian" value="0" :disabled="useHarian">
                    </div>
                    <div class="flex flex-col items-center">
                        <label class="flex items-center gap-1 cursor-pointer mb-1" title="Centang untuk mengaktifkan nilai Tugas">
                            <input type="checkbox" x-model="useTugas" @change="if(!useTugas) { pTugas = 0; document.querySelectorAll('.input-tugas').forEach(el => el.value = 0); } else if(pTugas <= 0) { pTugas = 20; }" class="w-3 h-3 text-blue-600 rounded focus:ring-blue-500 cursor-pointer">
                            <span class="text-[10px] font-bold text-blue-800">Tugas (%)</span>
                        </label>
                        <input type="number" name="p_tugas" x-model.number="pTugas" min="0" max="100" x-show="useTugas" :disabled="!useTugas" class="input-compact w-16 text-center text-xs">
                        <input type="hidden" name="p_tugas" value="0" :disabled="useTugas">
                    </div>
                    <div class="flex flex-col items-center">
                        <label class="flex items-center gap-1 cursor-pointer mb-1" title="Centang untuk mengaktifkan nilai Quiz">
                            <input type="checkbox" x-model="useQuiz" @change="if(!useQuiz) { pQuiz = 0; document.querySelectorAll('.input-quiz').forEach(el => el.value = 0); } else if(pQuiz <= 0) { pQuiz = 20; }" class="w-3 h-3 text-blue-600 rounded focus:ring-blue-500 cursor-pointer">
                            <span class="text-[10px] font-bold text-blue-800">Quiz (%)</span>
                        </label>
                        <input type="number" name="p_quiz" x-model.number="pQuiz" min="0" max="100" x-show="useQuiz" :disabled="!useQuiz" class="input-compact w-16 text-center text-xs">
                        <input type="hidden" name="p_quiz" value="0" :disabled="useQuiz">
                    </div>
                    <div class="flex flex-col items-center">
                        <label class="flex items-center gap-1 cursor-pointer mb-1" title="Centang untuk mengaktifkan nilai Proyek">
                            <input type="checkbox" x-model="useProyek" @change="if(!useProyek) { pProyek = 0; document.querySelectorAll('.input-proyek').forEach(el => el.value = 0); } else if(pProyek <= 0) { pProyek = 20; }" class="w-3 h-3 text-blue-600 rounded focus:ring-blue-500 cursor-pointer">
                            <span class="text-[10px] font-bold text-blue-800">Proyek (%)</span>
                        </label>
                        <input type="number" name="p_proyek" x-model.number="pProyek" min="0" max="100" x-show="useProyek" :disabled="!useProyek" class="input-compact w-16 text-center text-xs">
                        <input type="hidden" name="p_proyek" value="0" :disabled="useProyek">
                    </div>
                    <div class="flex flex-col items-center bg-amber-100/60 p-1 rounded border border-amber-200" x-show="sertakanUlangan" style="display: none;">
                        <label class="flex items-center gap-1 cursor-pointer mb-1" title="Bobot Persentase Nilai Ulangan / Ujian">
                            <span class="text-[10px] font-black text-amber-900">Ulangan (%)</span>
                        </label>
                        <input type="number" name="p_ulangan" x-model.number="pUlangan" min="0" max="100" class="input-compact w-16 text-center text-xs font-bold text-amber-900 bg-white border border-amber-300">
                    </div>
                </div>

                <!-- Checkbox & Tombol Tarik Ulangan -->
                <div class="flex items-center gap-2">
                    <div class="flex items-center bg-blue-50 px-3 py-2 rounded border border-blue-100 self-center">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="sertakan_ulangan" value="1" x-model="sertakanUlangan" @change="if(sertakanUlangan) { if(pUlangan <= 0) { pUlangan = 20; } fetchRataUjian(); } else { pUlangan = 0; }" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 cursor-pointer">
                            <span class="text-xs font-bold text-blue-800">Sertakan Nilai Ulangan</span>
                        </label>
                    </div>

                    <button type="button" @click="sertakanUlangan = true; if(pUlangan <= 0) { pUlangan = 20; } fetchRataUjian(true);" onclick="enableSertakanUlanganAndFetch(true)" class="btn-compact bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs whitespace-nowrap shadow-sm flex items-center gap-1 border border-amber-600 self-center py-2 px-3 rounded-lg cursor-pointer" title="Tarik dan tampilkan nilai ulangan siswa dari database">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        📥 Tarik Nilai
                    </button>
                </div>
            </div>
        </div>

        <!-- Info Formula -->
        <div class="mb-3 text-xs flex flex-col sm:flex-row justify-between items-start sm:items-center gap-1.5 p-2 rounded-lg border" :class="totalBobotAktif() > 0 ? 'bg-blue-50/70 border-blue-200 text-blue-900' : 'bg-red-50 border-red-200 text-red-600 font-bold'">
            <span class="italic">
                * <strong>Rumus Otomatis:</strong> Nilai Akhir dihitung proporsional dari 
                <span class="font-bold text-blue-800 underline">komponen yang dicentang saja</span> 
                (Total Bobot Aktif: <strong class="text-blue-950 font-black" x-text="totalBobotAktif() + '%'"></strong>).
            </span>
            <span x-show="totalBobotAktif() <= 0" class="text-red-600 font-extrabold">⚠️ Minimal centang 1 komponen penilaian!</span>
        </div>

        <!-- Compact Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse table-compact">
                <thead>
                    <tr>
                        <th class="w-10 text-center">No</th>
                        <th>Nomer Induk</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th class="w-24 text-center bg-blue-50/50 text-blue-900 font-bold" x-show="useHarian">Harian</th>
                        <th class="w-24 text-center" x-show="useTugas">Tugas</th>
                        <th class="w-24 text-center" x-show="useQuiz">Quiz</th>
                        <th class="w-24 text-center" x-show="useProyek">Proyek</th>
                        <th class="w-24 text-center bg-amber-50 text-amber-800" x-show="sertakanUlangan">Ulangan</th>
                        <th class="w-24 text-center bg-blue-50">Nilai Akhir</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($siswas as $index => $siswa)
                    <tr class="hover:bg-slate-50 siswa-row" data-kelas="{{ $siswa->kelas_id }}" data-siswa="{{ $siswa->id }}" x-data="{ harian: {{ $siswa->rata_harian ?? 80 }}, tugas: 0, quiz: 0, proyek: 0, ulangan: 0 }">
                        <td class="text-center text-slate-500">{{ $index + 1 }}</td>
                        <td class="font-mono text-slate-600">{{ $siswa->nis }}</td>
                        <td class="font-medium text-slate-800">{{ $siswa->user->name ?? '-' }}</td>
                        <td class="text-slate-600">{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                        <td class="p-1" x-show="useHarian">
                            <input type="number" x-model.number="harian" name="nilai[{{ $siswa->id }}][harian]" min="0" max="100" class="input-compact input-harian w-full text-center bg-blue-50/40 font-bold text-blue-900 border-blue-200" placeholder="80">
                        </td>
                        <td class="p-1" x-show="useTugas">
                            <input type="number" x-model.number="tugas" name="nilai[{{ $siswa->id }}][tugas]" min="0" max="100" class="input-compact input-tugas w-full text-center bg-white" placeholder="0">
                        </td>
                        <td class="p-1" x-show="useQuiz">
                            <input type="number" x-model.number="quiz" name="nilai[{{ $siswa->id }}][quiz]" min="0" max="100" class="input-compact input-quiz w-full text-center bg-white" placeholder="0">
                        </td>
                        <td class="p-1" x-show="useProyek">
                            <input type="number" x-model.number="proyek" name="nilai[{{ $siswa->id }}][proyek]" min="0" max="100" class="input-compact input-proyek w-full text-center bg-white" placeholder="0">
                        </td>
                        <td class="p-1 bg-amber-50/50 text-center font-bold text-amber-900" x-show="sertakanUlangan">
                            <span class="input-ulangan-val inline-block py-1 px-2.5 rounded-lg font-mono font-black text-xs shadow-xs" :class="ulangan > 0 ? 'bg-green-100 border border-green-400 text-green-900' : 'bg-slate-100 border border-slate-300 text-slate-500'" x-text="parseFloat(ulangan).toFixed(1)">0.0</span>
                            <input type="hidden" name="nilai[{{ $siswa->id }}][ulangan]" :value="ulangan">
                        </td>
                        <td class="p-1" :class="parseFloat(hitungNilai(harian, tugas, quiz, proyek, ulangan)) < {{ $kkm }} ? 'bg-red-50/50' : 'bg-blue-50/50'">
                            <div class="w-full text-center font-bold py-1" 
                                 title="Prediksi Nilai Akhir"
                                 :class="parseFloat(hitungNilai(harian, tugas, quiz, proyek, ulangan)) < {{ $kkm }} ? 'text-red-600' : 'text-blue-700'"
                                 x-text="hitungNilai(harian, tugas, quiz, proyek, ulangan)">0.0</div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-slate-500 italic">
                            @if(request('kelas_id'))
                                Belum ada data siswa di kelas ini.
                            @else
                                Silakan pilih kelas terlebih dahulu untuk menampilkan data nilai.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Bottom Action Bar -->
        <div class="mt-4 pt-4 border-t border-slate-200 flex flex-col md:flex-row items-center justify-between gap-3 bg-slate-50 p-4 rounded-lg">
            <div class="text-xs text-slate-600">
                <span class="font-bold text-slate-800">📌 Catatan Simpan Nilai:</span> Nilai akhir otomatis dihitung dari persentase komponen yang dicentang.
            </div>
            <button type="submit" 
                    :disabled="totalBobotAktif() <= 0" 
                    :class="totalBobotAktif() <= 0 ? 'bg-slate-300 border-slate-300 text-slate-500 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700 text-white font-black shadow-md cursor-pointer'" 
                    class="px-6 py-2.5 rounded-lg border text-sm flex items-center justify-center gap-2 transition-all w-full md:w-auto">
                <svg x-show="!isSaving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                <svg x-show="isSaving" style="display:none;" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span x-text="isSaving ? 'Menyimpan...' : '💾 Simpan Nilai Akhir'"></span>
            </button>
        </div>
    </form>
    </div>

    <!-- Tab Riwayat -->
    <div x-show="tab === 'riwayat'" x-cloak class="bg-white rounded-b rounded-tr border border-slate-200 shadow-sm p-4">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-3">
            <h3 class="font-bold text-slate-700 text-sm">Semua Riwayat Nilai</h3>
            
            <!-- Filters -->
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3 w-full md:w-auto items-stretch sm:items-center">
                <select id="filterRiwayatBab" onchange="filterRiwayat()" class="input-compact bg-slate-50 w-full sm:w-48 cursor-pointer font-semibold">
                    <option value="all">Semua Materi / Bab</option>
                    @foreach($daftar_bab as $bab)
                        <option value="{{ $bab }}">{{ $bab }}</option>
                    @endforeach
                </select>
                
                <select id="filterRiwayatKelas" onchange="filterRiwayat()" class="input-compact bg-slate-50 w-full sm:w-32 cursor-pointer font-semibold">
                    <option value="all">Semua Kelas</option>
                    @foreach($kelas as $k)
                        <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>

                <form action="{{ route('guru.nilai.destroy-by-bab') }}" method="POST" id="formHapusBab" onsubmit="return confirmHapusBab();" class="inline">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="bab" id="hiddenHapusBab">
                    <button type="submit" id="btnHapusBab" disabled title="Pilih Materi / Bab terlebih dahulu untuk menghapus" class="btn-compact bg-red-100 text-red-400 border border-red-200 shadow-sm flex items-center text-xs opacity-60 cursor-not-allowed whitespace-nowrap">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Hapus Semua Nilai Bab Ini
                    </button>
                </form>
            </div>
        </div>

        @if($riwayat_nilai->isEmpty())
        <div class="border border-dashed border-slate-300 p-8 rounded text-center text-slate-500 text-sm">
            Belum ada riwayat nilai yang diinput.
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse table-compact">
                <thead>
                    <tr>
                        <th class="w-10 text-center">No</th>
                        <th>Tanggal Input</th>
                        <th>Siswa</th>
                        <th>Kelas</th>
                        <th>Bab / Materi</th>
                        <th class="text-center">Tugas</th>
                        <th class="text-center">Quiz</th>
                        <th class="text-center">Proyek</th>
                        <th class="text-center">Ulangan</th>
                        <th class="text-center bg-blue-50">Nilai Akhir</th>
                        <th class="text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($riwayat_nilai as $index => $rn)
                    <tr class="hover:bg-slate-50 riwayat-row" data-kelas="{{ $rn->siswa->kelas_id }}" data-bab="{{ strtolower($rn->bab) }}">
                        <td class="text-center text-slate-500">{{ $index + 1 }}</td>
                        <td class="text-slate-600 text-xs">{{ $rn->created_at->format('d M Y H:i') }}</td>
                        <td class="font-medium text-slate-800">{{ $rn->siswa->user->name ?? '-' }}</td>
                        <td class="text-slate-600">{{ $rn->siswa->kelas->nama_kelas ?? '-' }}</td>
                        <td class="font-bold text-slate-700">{{ $rn->bab }}</td>
                        <td class="text-center {{ $rn->tugas < $kkm ? 'text-red-600 font-bold' : '' }}">{{ $rn->tugas }}</td>
                        <td class="text-center {{ $rn->quiz < $kkm ? 'text-red-600 font-bold' : '' }}">{{ $rn->quiz }}</td>
                        <td class="text-center {{ $rn->proyek < $kkm ? 'text-red-600 font-bold' : '' }}">{{ $rn->proyek }}</td>
                        <td class="text-center {{ $rn->ulangan < $kkm ? 'text-red-600 font-bold' : '' }}">{{ $rn->ulangan }}</td>
                        <td class="text-center font-bold {{ $rn->nilai_akhir < $kkm ? 'text-red-700 bg-red-50' : 'text-slate-800 bg-slate-50/50' }}">{{ $rn->nilai_akhir }}</td>
                        <td class="text-center">
                            <div class="flex items-center justify-center space-x-1">
                                <button type="button" 
                                    @click="editData = { id: '{{ $rn->id }}', siswa_id: '{{ $rn->siswa_id }}', siswa_nama: {{ json_encode($rn->siswa->user->name ?? '-') }}, bab: {{ json_encode($rn->bab) }}, tugas: '{{ $rn->tugas }}', quiz: '{{ $rn->quiz }}', proyek: '{{ $rn->proyek }}', ulangan: '{{ $rn->ulangan }}' }; editModalOpen = true; fetchRataUjianForEdit({{ json_encode($rn->bab) }})" 
                                    class="btn-compact bg-amber-500 hover:bg-amber-600 text-white text-xs">Ubah</button>
                                <form action="{{ url('/app/nilai', $rn->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus nilai ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 hover:bg-red-50 p-1 rounded" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <!-- Tab Rekap Semester -->
    <div x-show="tab === 'rekap'" x-cloak class="bg-white rounded-b rounded-tr border border-slate-200 shadow-sm p-4">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-3">
            <div>
                <h3 class="font-bold text-slate-700 text-sm">Rekapitulasi Nilai Akhir Semester</h3>
                <p class="text-xs text-slate-500 mt-1">Rata-rata dari seluruh materi/bab yang telah dinilai.</p>
            </div>
            
            <div class="w-full md:w-auto flex items-center gap-2">
                <select id="filterRekapKelas" onchange="filterRekap()" class="input-compact bg-slate-50 w-full md:w-48 cursor-pointer">
                    <option value="all">Semua Kelas</option>
                    @foreach($kelas as $k)
                        <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>

                <button type="button" onclick="exportRekapNilai()" class="btn-compact bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs px-3 py-1.5 rounded shadow-sm flex items-center gap-1.5 whitespace-nowrap">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Export CSV
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse table-compact">
                <thead>
                    <tr>
                        <th class="w-10 text-center">No</th>
                        <th>Nomer Induk</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th class="text-center">Total Bab</th>
                        <th class="text-center bg-blue-50 w-32">Rata-rata Rapor</th>
                        <th class="text-center w-24">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($siswas as $index => $siswa)
                        @php
                            $total_bab = $siswa->nilais->count();
                            $rata_rata = $total_bab > 0 ? round($siswa->nilais->avg('nilai_akhir'), 1) : 0;
                            $lulus = $rata_rata >= $kkm;
                        @endphp
                        <tr class="hover:bg-slate-50 rekap-row" data-kelas="{{ $siswa->kelas_id }}">
                            <td class="text-center text-slate-500">{{ $index + 1 }}</td>
                            <td class="font-mono text-slate-600">{{ $siswa->nis }}</td>
                            <td class="font-medium text-slate-800">{{ $siswa->user->name ?? '-' }}</td>
                            <td class="text-slate-600">{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                            <td class="text-center font-medium">{{ $total_bab }}</td>
                            <td class="text-center font-bold text-lg {{ $rata_rata < $kkm && $total_bab > 0 ? 'text-red-600 bg-red-50/50' : ($total_bab > 0 ? 'text-blue-700 bg-blue-50/50' : 'text-slate-400') }}">
                                {{ $total_bab > 0 ? $rata_rata : '-' }}
                            </td>
                            <td class="text-center">
                                @if($total_bab == 0)
                                    <span class="text-xs text-slate-400 italic">Belum dinilai</span>
                                @elseif($lulus)
                                    <span class="inline-block px-2 py-1 bg-green-100 text-green-800 text-[10px] font-bold rounded">TUNTAS</span>
                                @else
                                    <span class="inline-block px-2 py-1 bg-red-100 text-red-800 text-[10px] font-bold rounded">TDK TUNTAS</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Modal -->
    <div x-show="editModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 shadow-xl w-full max-w-md mx-4" @click.away="editModalOpen = false">
            <div class="flex justify-between items-center mb-4 pb-2 border-b border-slate-100">
                <h3 class="font-bold text-slate-800 text-lg">✏️ Ubah Nilai Siswa</h3>
                <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 font-bold">&times;</button>
            </div>
            
            <form :action="`{{ url('/app/nilai') }}/${editData.id}`" method="POST">
                @csrf
                @method('PUT')
                
                <input type="hidden" name="p_harian" :value="pHarian">
                <input type="hidden" name="p_tugas" :value="pTugas">
                <input type="hidden" name="p_quiz" :value="pQuiz">
                <input type="hidden" name="p_proyek" :value="pProyek">
                <input type="hidden" name="p_ulangan" :value="pUlangan">

                <div class="mb-3 bg-blue-50 p-2.5 rounded-md border border-blue-100">
                    <div class="text-xs text-blue-700 font-semibold mb-0.5">Nama Siswa:</div>
                    <div class="text-sm font-bold text-blue-950" x-text="editData.siswa_nama || 'Siswa'"></div>
                </div>

                <div class="mb-3">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Materi / Bab</label>
                    <input type="text" name="bab" x-model="editData.bab" required class="input-compact w-full bg-slate-50 font-semibold text-slate-800">
                </div>

                <!-- Checkbox Ulangan in edit modal -->
                <div class="mb-3 flex items-center gap-2 bg-amber-50/80 p-2 rounded border border-amber-200">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="sertakan_ulangan" value="1" x-model="sertakanUlangan" class="w-4 h-4 text-amber-600 rounded focus:ring-amber-500 cursor-pointer">
                        <span class="text-xs font-bold text-amber-900">Sertakan Nilai Ulangan dalam Perhitungan</span>
                    </label>
                </div>
                
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs font-semibold text-blue-800 mb-1">Harian / Keaktifan</label>
                        <input type="number" name="harian" x-model.number="editData.harian" min="0" max="100" class="input-compact w-full bg-blue-50/60 text-center font-bold text-blue-900 border-blue-200" placeholder="80">
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nilai Tugas</label>
                        <input type="number" name="tugas" x-model.number="editData.tugas" min="0" max="100" required class="input-compact w-full bg-white text-center font-bold">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nilai Quiz</label>
                        <input type="number" name="quiz" x-model.number="editData.quiz" min="0" max="100" required class="input-compact w-full bg-white text-center font-bold">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nilai Proyek</label>
                        <input type="number" name="proyek" x-model.number="editData.proyek" min="0" max="100" required class="input-compact w-full bg-white text-center font-bold">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-amber-800 mb-1">Nilai Ulangan</label>
                        <input type="number" name="ulangan" x-model.number="editData.ulangan" min="0" max="100" class="input-compact w-full bg-amber-50 text-center font-bold text-amber-900 border-amber-300" placeholder="Opsional">
                    </div>
                </div>

                <div class="mb-4 text-xs text-blue-800 bg-blue-50/80 p-3 rounded-lg border border-blue-100 flex justify-between items-center">
                    <span class="font-semibold" title="Prediksi Nilai Akhir">Prediksi Nilai Akhir Baru:</span>
                    <strong class="text-base text-blue-900 font-black" x-text="hitungNilai(editData.harian, editData.tugas, editData.quiz, editData.proyek, editData.ulangan)">0.0</strong>
                </div>
                
                <div class="flex justify-end gap-2">
                    <button type="button" @click="editModalOpen = false" class="btn-compact bg-slate-200 hover:bg-slate-300 text-slate-700">Batal</button>
                    <button type="submit" class="btn-compact bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- KKM Modal -->
    <div x-show="kkmModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded p-6 shadow-xl w-full max-w-sm mx-4" @click.away="kkmModalOpen = false">
            <h3 class="font-bold text-slate-800 text-lg mb-2">Pengaturan KKM</h3>
            <p class="text-xs text-slate-500 mb-4">Kriteria Ketuntasan Minimal akan digunakan untuk menentukan batas lulus nilai siswa pada tabel nilai dan rapor.</p>
            
            <form action="{{ url('/app/setting-kkm') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Batas Nilai KKM</label>
                    <input type="number" name="kkm" value="{{ $kkm }}" min="0" max="100" required class="input-compact w-full text-lg p-2 font-bold text-center">
                </div>
                
                <div class="flex justify-end gap-2">
                    <button type="button" @click="kkmModalOpen = false" class="btn-compact bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2">Batal</button>
                    <button type="submit" class="btn-compact bg-amber-500 hover:bg-amber-600 text-white shadow-sm px-4 py-2 font-medium">Simpan KKM</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
@php
    $riwayatJsonData = $riwayat_nilai->map(function($r) { 
        return [
            'siswa_id' => $r->siswa_id, 
            'bab' => strtolower($r->bab), 
            'tugas' => $r->tugas, 
            'quiz' => $r->quiz, 
            'proyek' => $r->proyek
        ]; 
    })->values()->all();
@endphp
    const riwayatData = @json($riwayatJsonData);
    const babSudahAdaPerKelas = @json($babSudahAdaPerKelas ?? []);
    const currentKelasId = {{ request('kelas_id', 0) }};

    function checkBabExistsWarning() {
        const babInput = document.getElementById('inputBabUtama')?.value?.trim() || '';
        const warningEl = document.getElementById('warningBabSudahAda');
        const inputForceUpdate = document.getElementById('inputForceUpdate');
        
        if (!warningEl) return;
        
        if (!babInput) {
            warningEl.className = "hidden";
            return;
        }

        const existingList = (babSudahAdaPerKelas[currentKelasId] || []).map(b => b.toLowerCase());
        const isMatch = existingList.includes(babInput.toLowerCase());

        if (isMatch) {
            if (inputForceUpdate && inputForceUpdate.value === '1') {
                warningEl.className = "mt-1.5 text-[11px] font-semibold text-blue-800 bg-blue-50 border border-blue-200 px-2.5 py-1 rounded block";
                warningEl.innerHTML = "✏️ <strong>Mode Update Aktif:</strong> Anda sedang memperbarui nilai tersimpan untuk Bab ini.";
            } else {
                warningEl.className = "mt-1.5 text-[11px] font-semibold text-amber-800 bg-amber-50 border border-amber-300 px-2.5 py-1 rounded block";
                warningEl.innerHTML = "⚠️ <strong>Nilai Bab ini sudah ada untuk kelas ini.</strong> Klik tombol <strong>Muat Data</strong> jika ingin mengedit nilai yang tersimpan.";
            }
        } else {
            warningEl.className = "hidden";
            if (inputForceUpdate) inputForceUpdate.value = '0';
        }
    }

    function fetchRataUjian(showToast = false) {
        const babInput = document.getElementById('inputBabUtama')?.value?.trim() || '';
        const fetchUrl = `{{ url('/app/nilai/rata-ujian') }}?bab=${encodeURIComponent(babInput)}`;

        fetch(fetchUrl, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(async res => {
                const contentType = res.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return res.json();
                }
                const text = await res.text();
                throw new Error(text.includes('<!DOCTYPE') ? 'Sesi login telah berakhir atau URL tidak dapat diakses. Silakan refresh halaman dan pastikan Anda sudah login kembali.' : text);
            })
            .then(data => {
                const el = document.querySelector('[x-data]');
                if (el) {
                    if (window.Alpine && typeof Alpine.$data === 'function') {
                        const rootData = Alpine.$data(el);
                        if (rootData) {
                            rootData.rataUjian = Object.assign({}, data);
                        }
                    } else if (el.__x) {
                        el.__x.$data.rataUjian = Object.assign({}, data);
                    }
                }

                // Direct DOM & Alpine state update for 100% reliable rendering
                let foundCount = 0;
                document.querySelectorAll('.siswa-row').forEach(row => {
                    const siswaId = row.dataset.siswa;
                    const val = (data && data[siswaId] !== undefined) ? parseFloat(data[siswaId]).toFixed(1) : '0.0';
                    const valNum = (data && data[siswaId] !== undefined) ? parseFloat(data[siswaId]) : 0;
                    
                    if (valNum > 0) foundCount++;

                    // Bind directly to Alpine local row component state
                    if (window.Alpine && typeof Alpine.$data === 'function') {
                        const rowData = Alpine.$data(row);
                        if (rowData) {
                            rowData.ulangan = valNum;
                        }
                    } else if (row.__x) {
                        row.__x.$data.ulangan = valNum;
                    }

                    const scoreSpan = row.querySelector('.input-ulangan-val');
                    if (scoreSpan) {
                        scoreSpan.textContent = val;
                        if (valNum > 0) {
                            scoreSpan.className = "input-ulangan-val inline-block py-1 px-2.5 rounded-lg bg-green-100 border border-green-400 font-mono font-black text-green-900 text-xs shadow-sm";
                        } else {
                            scoreSpan.className = "input-ulangan-val inline-block py-1 px-2.5 rounded-lg bg-slate-100 border border-slate-300 font-mono font-bold text-slate-500 text-xs";
                        }
                    }
                });

                if (showToast) {
                    if (foundCount > 0) {
                        alert(`Berhasil menarik nilai ulangan! Ditemukan ${foundCount} siswa yang memiliki nilai ujian.`);
                    } else {
                        alert(`Penarikan nilai selesai, namun belum ada data nilai ujian yang tersimpan untuk Bab ini di database (Pastikan siswa sudah selesai mengerjakan ujian / dinilai).`);
                    }
                }
            })
            .catch(err => {
                console.error('Error fetching exam averages:', err);
                if (showToast) alert('Gagal menarik nilai ulangan: ' + err.message);
            });
    }

    function fetchRataUjianForEdit(bab) {
        const babVal = bab || document.getElementById('inputBabUtama')?.value?.trim() || '';
        const fetchUrl = `{{ url('/app/nilai/rata-ujian') }}?bab=${encodeURIComponent(babVal)}`;

        fetch(fetchUrl, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(res => {
                if (res.headers.get('content-type')?.includes('application/json')) {
                    return res.json();
                }
                return {};
            })
            .then(data => {
                const el = document.querySelector('[x-data]');
                if (el) {
                    if (window.Alpine && typeof Alpine.$data === 'function') {
                        const rootData = Alpine.$data(el);
                        if (rootData) {
                            rootData.rataUjian = Object.assign({}, data);
                        }
                    } else if (el.__x) {
                        el.__x.$data.rataUjian = Object.assign({}, data);
                    }
                }

                // Sync ke setiap baris siswa
                document.querySelectorAll('.siswa-row').forEach(row => {
                    const siswaId = parseInt(row.dataset.siswa);
                    const val = (data && data[siswaId] !== undefined) ? parseFloat(data[siswaId]) : 0;
                    
                    if (window.Alpine && typeof Alpine.$data === 'function') {
                        const rowData = Alpine.$data(row);
                        if (rowData) {
                            rowData.ulangan = val;
                        }
                    } else if (row.__x) {
                        row.__x.$data.ulangan = val;
                    }

                    const valSpan = row.querySelector('.input-ulangan-val');
                    if (valSpan) {
                        valSpan.textContent = val.toFixed(1);
                        if (val > 0) {
                            valSpan.className = 'input-ulangan-val inline-block py-1 px-2.5 rounded-lg font-mono font-black text-xs shadow-xs bg-green-100 border border-green-400 text-green-900';
                        } else {
                            valSpan.className = 'input-ulangan-val inline-block py-1 px-2.5 rounded-lg font-mono font-black text-xs shadow-xs bg-slate-100 border border-slate-300 text-slate-500';
                        }
                    }
                    const hiddenInput = row.querySelector('input[name*="[ulangan]"]');
                    if (hiddenInput) {
                        hiddenInput.value = val;
                    }
                });
            })
            .catch(err => console.error('Error fetching exam averages:', err));
    }

    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            fetchRataUjian(false);
            checkBabExistsWarning();
        }, 300);
    });

    function enableSertakanUlanganAndFetch(showToast = false) {
        const el = document.querySelector('[x-data]');
        if (el) {
            if (window.Alpine && typeof Alpine.$data === 'function') {
                const rootData = Alpine.$data(el);
                if (rootData) {
                    rootData.sertakanUlangan = true;
                }
            } else if (el.__x) {
                el.__x.$data.sertakanUlangan = true;
            }
        }
        fetchRataUjian(showToast);
    }

    function loadExistingGrades() {
        const babInput = document.getElementById('inputBabUtama').value.trim();
        if (!babInput) {
            alert('Silakan ketik atau pilih nama Materi / Bab terlebih dahulu.');
            return;
        }
        
        enableSertakanUlanganAndFetch();
        const bab = babInput.toLowerCase();
        
        document.querySelectorAll('.siswa-row').forEach(row => {
            const siswaId = parseInt(row.dataset.siswa);
            const grade = riwayatData.find(g => g.siswa_id === siswaId && g.bab === bab);
            
            const tugasInput = row.querySelector('.input-tugas');
            const quizInput = row.querySelector('.input-quiz');
            const proyekInput = row.querySelector('.input-proyek');
            
            if (grade) {
                tugasInput.value = grade.tugas;
                quizInput.value = grade.quiz;
                proyekInput.value = grade.proyek;

                if (window.Alpine && typeof Alpine.$data === 'function') {
                    const rowData = Alpine.$data(row);
                    if (rowData) {
                        rowData.tugas = parseFloat(grade.tugas) || 0;
                        rowData.quiz = parseFloat(grade.quiz) || 0;
                        rowData.proyek = parseFloat(grade.proyek) || 0;
                        if (grade.ulangan > 0) rowData.ulangan = parseFloat(grade.ulangan) || 0;
                    }
                } else if (row.__x) {
                    row.__x.$data.tugas = parseFloat(grade.tugas) || 0;
                    row.__x.$data.quiz = parseFloat(grade.quiz) || 0;
                    row.__x.$data.proyek = parseFloat(grade.proyek) || 0;
                    if (grade.ulangan > 0) row.__x.$data.ulangan = parseFloat(grade.ulangan) || 0;
                }
            } else {
                tugasInput.value = 0;
                quizInput.value = 0;
                proyekInput.value = 0;

                if (window.Alpine && typeof Alpine.$data === 'function') {
                    const rowData = Alpine.$data(row);
                    if (rowData) {
                        rowData.tugas = 0;
                        rowData.quiz = 0;
                        rowData.proyek = 0;
                    }
                } else if (row.__x) {
                    row.__x.$data.tugas = 0;
                    row.__x.$data.quiz = 0;
                    row.__x.$data.proyek = 0;
                }
            }
            
            // Trigger AlpineJS x-model update
            tugasInput.dispatchEvent(new Event('input'));
            quizInput.dispatchEvent(new Event('input'));
            proyekInput.dispatchEvent(new Event('input'));
        });

        const inputForceUpdate = document.getElementById('inputForceUpdate');
        if (inputForceUpdate) inputForceUpdate.value = '1';
        checkBabExistsWarning();
    }

    function filterKelas() {
        const selected = document.getElementById('kelasFilter').value;
        const rows = document.querySelectorAll('.siswa-row');
        rows.forEach(row => {
            const inputs = row.querySelectorAll('input');
            if (selected === 'all' || row.dataset.kelas === selected) {
                row.style.display = '';
                inputs.forEach(input => input.disabled = false);
            } else {
                row.style.display = 'none';
                inputs.forEach(input => input.disabled = true);
            }
        });
    }

    function filterRiwayat() {
        const selectedKelas = document.getElementById('filterRiwayatKelas').value;
        const selectedBab = document.getElementById('filterRiwayatBab').value;
        const rows = document.querySelectorAll('.riwayat-row');
        
        const btnHapusBab = document.getElementById('btnHapusBab');
        const hiddenHapusBab = document.getElementById('hiddenHapusBab');

        if (selectedBab !== 'all' && selectedBab.trim() !== '') {
            hiddenHapusBab.value = selectedBab;
            btnHapusBab.disabled = false;
            btnHapusBab.className = "btn-compact bg-red-600 hover:bg-red-700 text-white font-extrabold border border-red-700 shadow-sm flex items-center text-xs opacity-100 cursor-pointer whitespace-nowrap";
            btnHapusBab.title = "Hapus seluruh nilai siswa untuk Bab: " + selectedBab;
        } else {
            hiddenHapusBab.value = '';
            btnHapusBab.disabled = true;
            btnHapusBab.className = "btn-compact bg-red-100 text-red-400 border border-red-200 shadow-sm flex items-center text-xs opacity-60 cursor-not-allowed whitespace-nowrap";
            btnHapusBab.title = "Pilih Materi / Bab terlebih dahulu untuk menghapus";
        }

        rows.forEach(row => {
            const rowKelas = row.dataset.kelas;
            const rowBab = (row.dataset.bab || '').toLowerCase();
            const targetBab = selectedBab.toLowerCase();
            
            const matchKelas = (selectedKelas === 'all' || rowKelas === selectedKelas);
            const matchBab = (selectedBab === 'all' || rowBab === targetBab || rowBab.includes(targetBab));
            
            if (matchKelas && matchBab) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function confirmHapusBab() {
        const selectedBab = document.getElementById('hiddenHapusBab').value;
        if (!selectedBab) {
            alert('Silakan pilih Materi / Bab yang akan dihapus terlebih dahulu.');
            return false;
        }
        return confirm("Apakah Anda YAKIN ingin menghapus SELURUH nilai siswa untuk Bab '" + selectedBab + "'?\n\nTindakan ini akan menghapus semua catatan nilai harian/tugas/quiz/ulangan siswa pada bab tersebut dan tidak dapat dibatalkan.");
    }

    function filterRekap() {
        const selectedKelas = document.getElementById('filterRekapKelas').value;
        const rows = document.querySelectorAll('.rekap-row');
        
        rows.forEach(row => {
            const rowKelas = row.dataset.kelas;
            if (selectedKelas === 'all' || rowKelas === selectedKelas) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function exportRekapNilai() {
        const selectedKelas = document.getElementById('filterRekapKelas').value;
        if (selectedKelas === 'all' || !selectedKelas) {
            alert('Silakan pilih salah satu kelas spesifik pada dropdown filter untuk mengekspor rekap nilai.');
            return;
        }
        window.location.href = "{{ url('/app/nilai/export') }}?kelas_id=" + selectedKelas;
    }
</script>
@endsection
