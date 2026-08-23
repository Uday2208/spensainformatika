@extends('layouts.app')
@section('title', 'Rekap Hasil & Koreksi Siswa')
@section('page_title', 'Rekap Hasil & Koreksi Siswa')
@section('content')

@if(session('success'))
<div class="mb-4 bg-emerald-50 text-emerald-800 p-3.5 rounded-xl text-sm border border-emerald-200 shadow-xs flex items-center gap-2">
    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
    <span class="font-medium">{{ session('success') }}</span>
</div>
@endif

@if($errors->any())
<div class="mb-4 bg-red-50 text-red-800 p-3.5 rounded-xl text-sm border border-red-200 shadow-xs">
    <div class="font-bold mb-1 flex items-center gap-1.5 text-red-700">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
        Peringatan:
    </div>
    <ul class="list-disc list-inside space-y-0.5 text-xs text-red-600">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<!-- Exam Summary Card -->
<div class="mb-4 bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-4">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <span class="text-xs bg-blue-50 text-blue-700 font-extrabold px-2.5 py-0.5 rounded-md border border-blue-100 uppercase">{{ $ujian->bab }}</span>
            <span class="text-xs text-slate-500 font-semibold">KKM: <span class="text-slate-700 font-bold">{{ $kkm }}</span></span>
        </div>
        <h2 class="text-lg sm:text-xl font-black text-slate-800">{{ $ujian->judul }}</h2>
        <p class="text-xs text-slate-500 mt-0.5">Rekap dan evaluasi capaian hasil belajar siswa per rombel / kelas.</p>
    </div>

    <div class="flex items-center gap-2.5 flex-wrap">
        @if($hasEssay && $ujian->isSelesai())
        <a href="{{ route('guru.ujian.koreksi', $ujian->id) }}{{ $selectedKelas ? '?kelas_id=' . $selectedKelas : '' }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold px-4 py-2.5 rounded-xl text-xs sm:text-sm shadow-xs transition-all flex items-center gap-1.5 border border-indigo-700">
            ✍️ Koreksi Essay Massal
        </a>
        @endif

        @if($ujian->isSelesai())
        <form action="{{ route('guru.ujian.finalisasi', $ujian->id) }}" method="POST" onsubmit="return confirm('Simpan & publish nilai ulangan ini ke Rekap Nilai Utama?');">
            @csrf
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold px-4 py-2.5 rounded-xl text-xs sm:text-sm shadow-xs transition-all border border-emerald-700 flex items-center gap-1.5">
                📌 Finalisasi & Push Nilai
            </button>
        </form>
        @endif

        <a href="{{ route('guru.hasil.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-4 py-2.5 rounded-xl text-xs sm:text-sm transition-all border border-slate-200/80">
            ← Kembali
        </a>
    </div>
</div>

<!-- Tab Pemilih Rombel / Kelas -->
@if(isset($kelasList) && $kelasList->count() > 0)
<div class="bg-white p-3 rounded-2xl border border-slate-200 shadow-xs mb-4">
    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-hide">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap mr-1 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            Pilih Kelas:
        </span>
        @foreach($kelasList as $k)
        @php
            $stat = $statsPerKelas->get($k->id);
            $totalMhs = $stat ? $stat->total : 0;
            $needGrade = $stat ? $stat->belum_dinilai : 0;
            $isActive = ($selectedKelas == $k->id);
        @endphp
        <a href="{{ route('guru.hasil.show', $ujian->id) }}?kelas_id={{ $k->id }}" 
           class="px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all flex items-center gap-2 {{ $isActive ? 'bg-blue-600 text-white shadow-sm ring-2 ring-blue-600/30' : 'bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
            <span>Kelas {{ $k->nama_kelas }}</span>
            @if($totalMhs > 0)
                @if($needGrade > 0)
                    <span class="px-1.5 py-0.5 rounded-md text-[10px] font-black {{ $isActive ? 'bg-amber-400 text-amber-950' : 'bg-amber-100 text-amber-800' }}">
                        {{ $needGrade }} perlu dinilai
                    </span>
                @else
                    <span class="px-1.5 py-0.5 rounded-md text-[10px] font-black {{ $isActive ? 'bg-blue-800 text-white' : 'bg-emerald-100 text-emerald-700' }}">
                        ✓ Lengkap
                    </span>
                @endif
            @else
                <span class="text-[10px] font-normal opacity-70">(0 siswa)</span>
            @endif
        </a>
        @endforeach
    </div>
</div>
@endif

@if(empty($selectedKelas))
    <!-- TAMPILAN KOSONG / BELUM PILIH KELAS (Menghemat Beban Server & Browser) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-8 sm:p-12 text-center">
        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-blue-100 shadow-xs">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
        </div>
        <h3 class="text-base sm:text-lg font-bold text-slate-800 mb-1">Silakan Pilih Kelas / Rombel Terlebih Dahulu</h3>
        <p class="text-xs sm:text-sm text-slate-500 max-w-lg mx-auto mb-6 leading-relaxed">
            Untuk menjaga performa aplikasi tetap cepat dan analisis nilai lebih fokus, silakan klik salah satu rombel/kelas di bawah ini untuk melihat rekap pengerjaan siswa.
        </p>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 max-w-2xl mx-auto">
            @foreach($kelasList as $k)
            @php
                $stat = $statsPerKelas->get($k->id);
                $totalMhs = $stat ? $stat->total : 0;
                $needGrade = $stat ? $stat->belum_dinilai : 0;
            @endphp
            <a href="{{ route('guru.hasil.show', $ujian->id) }}?kelas_id={{ $k->id }}" 
               class="p-4 rounded-xl border border-slate-200 bg-slate-50/60 hover:bg-blue-50/50 hover:border-blue-300 transition-all text-left group">
                <div class="font-bold text-slate-800 text-sm group-hover:text-blue-700 flex items-center justify-between">
                    <span>Kelas {{ $k->nama_kelas }}</span>
                    <span class="text-slate-400 group-hover:translate-x-0.5 transition-transform text-xs">→</span>
                </div>
                <div class="mt-2 text-xs">
                    @if($totalMhs > 0)
                        <span class="text-slate-500 font-medium">{{ $totalMhs }} peserta</span>
                        @if($needGrade > 0)
                            <span class="block text-amber-600 font-bold mt-0.5 text-[11px]">{{ $needGrade }} belum dikoreksi</span>
                        @else
                            <span class="block text-emerald-600 font-bold mt-0.5 text-[11px]">✓ Selesai dinilai</span>
                        @endif
                    @else
                        <span class="text-slate-400 italic">Belum ada peserta</span>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
    </div>
@else
    <!-- TAMPILAN HASIL UJIAN PER-KELAS -->
    @if($hasilUjians->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-8 text-center">
            <div class="w-12 h-12 bg-slate-100 text-slate-400 rounded-xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
            </div>
            <h3 class="font-bold text-slate-700 text-sm">Belum Ada Peserta di Kelas {{ $selectedKelasObj->nama_kelas ?? '' }}</h3>
            <p class="text-xs text-slate-500 mt-1">Belum ada siswa dari rombel ini yang mengerjakan atau menyelesaikan ujian ini.</p>
        </div>
    @else
        {{-- ============================================================
             STATISTIK ANALISIS KELAS TERPILIH (KPI CARDS)
             ============================================================ --}}
        @if(isset($stats) && $stats['total_peserta'] > 0)
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
            <!-- Rata-rata Nilai -->
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs flex flex-col justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Rata-Rata</span>
                <div class="flex items-baseline justify-between mt-1">
                    <span class="text-2xl font-black {{ $stats['rata_kelas'] >= $kkm ? 'text-emerald-600' : 'text-amber-600' }}">
                        {{ number_format($stats['rata_kelas'], 1) }}
                    </span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-blue-50 text-blue-700">Skor</span>
                </div>
            </div>

            <!-- Tingkat Ketuntasan -->
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs flex flex-col justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Ketuntasan</span>
                <div class="flex items-baseline justify-between mt-1">
                    <span class="text-2xl font-black {{ $stats['persen_tuntas'] >= 75 ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ $stats['persen_tuntas'] }}%
                    </span>
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-600">KKM: {{ $kkm }}</span>
                </div>
            </div>

            <!-- Tuntas / Lulus -->
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs flex flex-col justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Tuntas (≥ KKM)</span>
                <div class="flex items-baseline justify-between mt-1">
                    <span class="text-2xl font-black text-emerald-700">{{ $stats['tuntas_count'] }}</span>
                    <span class="text-[10px] font-bold text-slate-400">Siswa</span>
                </div>
            </div>

            <!-- Remedial -->
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs flex flex-col justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-red-500">Remedial (&lt; KKM)</span>
                <div class="flex items-baseline justify-between mt-1">
                    <span class="text-2xl font-black text-red-600">{{ $stats['belum_tuntas_count'] }}</span>
                    <span class="text-[10px] font-bold text-slate-400">Siswa</span>
                </div>
            </div>

            <!-- Nilai Tertinggi -->
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs flex flex-col justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600">Tertinggi</span>
                <div class="flex items-baseline justify-between mt-1">
                    <span class="text-2xl font-black text-indigo-700">{{ number_format($stats['nilai_tertinggi'], 1) }}</span>
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700">Maks</span>
                </div>
            </div>

            <!-- Nilai Terendah -->
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs flex flex-col justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Terendah</span>
                <div class="flex items-baseline justify-between mt-1">
                    <span class="text-2xl font-black text-slate-700">{{ number_format($stats['nilai_terendah'], 1) }}</span>
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-600">Min</span>
                </div>
            </div>
        </div>
        @endif

        <!-- Student Results Table Card with Instant Search -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 sm:p-5" x-data="{ searchQuery: '' }">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-4 mb-4 border-b border-slate-100 gap-3">
                <div>
                    <h3 class="font-black text-slate-800 text-base flex items-center gap-2">
                        <span>📋 Hasil Siswa: Kelas {{ $selectedKelasObj->nama_kelas ?? '' }}</span>
                        <span class="text-xs bg-blue-100 text-blue-800 font-extrabold px-2.5 py-0.5 rounded-full border border-blue-200">{{ $hasilUjians->count() }} Peserta</span>
                    </h3>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">Daftar nilai pengerjaan ulangan harian siswa di kelas ini.</p>
                </div>

                <!-- Instant Search Input -->
                <div class="relative w-full sm:w-64">
                    <input type="text" 
                           x-model="searchQuery" 
                           placeholder="Cari nama siswa..." 
                           class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 pl-8 focus:bg-white focus:outline-none focus:border-blue-400 transition-colors">
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 text-xs uppercase font-extrabold border-b border-slate-200">
                            <th class="py-3 px-4 w-12 text-center">No</th>
                            <th class="py-3 px-4">Nama Siswa</th>
                            <th class="py-3 px-4 text-center">Skor PG</th>
                            <th class="py-3 px-4 text-center">Skor Essay</th>
                            <th class="py-3 px-4 text-center">Nilai Akhir</th>
                            <th class="py-3 px-4 text-center">Status KKM</th>
                            <th class="py-3 px-4 text-center">Deteksi Tab Switch</th>
                            <th class="py-3 px-4 text-center w-36">Aksi Inspeksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm font-medium">
                        @foreach($hasilUjians as $index => $h)
                        @php
                            $sName = $h->siswa->user->name ?? 'Siswa';
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors" 
                            x-show="!searchQuery || '{{ strtolower(addslashes($sName)) }}'.includes(searchQuery.toLowerCase())">
                            <td class="py-4 px-4 text-center text-slate-400 font-bold text-xs">{{ $index + 1 }}</td>
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-extrabold text-slate-800">{{ $sName }}</span>
                                    @if($h->status !== 'dinilai')
                                    <span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-[10px] font-extrabold rounded-md border border-amber-200 uppercase">⏳ Belum Dikoreksi</span>
                                    @else
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[10px] font-bold rounded-md border border-slate-200 uppercase">✓ Sudah Dinilai</span>
                                    @endif
                                </div>
                                <span class="text-xs text-slate-400 font-mono block mt-0.5">NISN: {{ $h->siswa->nisn ?? $h->siswa->nis ?? '-' }}</span>
                            </td>
                            <td class="py-4 px-4 text-center font-extrabold text-slate-700">
                                {{ number_format($h->nilai_pg, 1) }}
                            </td>
                            <td class="py-4 px-4 text-center font-extrabold text-indigo-700">
                                {{ number_format($h->nilai_essay, 1) }}
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="text-base font-black px-3 py-1 rounded-xl border {{ $h->nilai_akhir >= $kkm ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200' }}">
                                    {{ number_format($h->nilai_akhir, 1) }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-center">
                                @if($h->nilai_akhir >= $kkm)
                                <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-full border border-emerald-200">LULUS</span>
                                @else
                                <span class="px-2.5 py-0.5 bg-red-100 text-red-800 text-xs font-bold rounded-full border border-red-200">REMEDIAL</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center">
                                @if($h->tab_switch_count > 0)
                                <span class="px-2.5 py-1 bg-red-100 text-red-800 text-xs font-black rounded-lg border border-red-200 inline-flex items-center justify-center gap-1">
                                    ⚠️ {{ $h->tab_switch_count }}x Switch
                                </span>
                                @else
                                <span class="text-xs text-slate-400 font-semibold">Aman (0x)</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center">
                                <a href="{{ route('guru.hasil.detail-siswa', [$ujian->id, $h->siswa_id]) }}" class="bg-blue-50 hover:bg-blue-100 text-blue-700 font-extrabold text-xs py-1.5 px-3 rounded-xl border border-blue-200 transition-all inline-block shadow-2xs">
                                    🔍 Periksa Jawaban
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endif

@endsection
