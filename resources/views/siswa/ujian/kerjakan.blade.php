@extends('layouts.app')
@section('title', 'Mengerjakan Ujian')
@section('page_title', '')
@section('content')

<!-- Hide Sidebar & Header for distraction-free exam environment -->
<style>
    /* Desktop sidebar and layout overrides */
    aside, 
    .bg-gradient-to-b.from-blue-900, 
    div[class*="w-[260px]"],
    [class*="translate-x-0"] {
        display: none !important;
    }
    header, 
    .h-\[60px\],
    [class*="h-[60px]"] {
        display: none !important;
    }
    main {
        padding: 0 !important;
    }
    .max-w-7xl {
        max-width: 100% !important;
        padding-bottom: 0 !important;
        padding: 0 !important;
    }
    body {
        overflow: auto !important;
        background-color: #f8fafc !important;
    }
</style>

<div class="min-h-screen bg-slate-50 flex flex-col pb-20 relative select-none" 
     x-data="ujianState({{ $sisaDetik }}, {{ $ujian->id }}, {{ Auth::user()->siswa->id ?? 0 }})" 
     x-init="initUjian()">
     
    <!-- Sticky Top Header -->
    <div class="sticky top-0 bg-white border-b border-slate-200 px-4 py-3 shadow-sm z-40 flex items-center justify-between">
        <div class="min-w-0">
            <h2 class="text-sm sm:text-base font-extrabold text-slate-800 truncate">{{ $ujian->judul }}</h2>
            <div class="flex items-center gap-2 mt-0.5">
                <span class="text-[10px] bg-slate-100 border border-slate-200 text-slate-600 px-2 py-0.5 rounded font-bold uppercase">{{ $ujian->bab }}</span>
                <!-- Auto save status indicator -->
                <span class="text-xs flex items-center gap-1.5" :class="saveStatus === 'saved' ? 'text-green-600' : (saveStatus === 'saving' ? 'text-blue-500' : 'text-red-500 font-bold')">
                    <span class="w-1.5 h-1.5 rounded-full" :class="saveStatus === 'saved' ? 'bg-green-500' : (saveStatus === 'saving' ? 'bg-blue-500' : 'bg-red-500')"></span>
                    <span x-text="saveStatusText"></span>
                </span>
            </div>
        </div>

        <!-- Right Side: Timer & Submit Button -->
        <div class="flex items-center gap-3">
            <!-- Timer -->
            <div class="bg-blue-50 border border-blue-200 text-blue-800 font-mono font-extrabold px-3 py-1.5 rounded-xl flex items-center gap-1.5 text-sm sm:text-base">
                <svg class="w-4 h-4 text-blue-600 animate-spin" style="animation-duration: 3s;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span x-text="timerText">00:00:00</span>
            </div>

            <!-- Submit -->
            <button @click="confirmSubmit()" 
                    :disabled="isSubmitting"
                    :class="{'opacity-50 cursor-not-allowed': isSubmitting, 'hover:bg-red-700': !isSubmitting}"
                    class="bg-red-600 text-white font-extrabold px-4 py-2 rounded-xl text-sm sm:text-base active:scale-[0.98] transition-all shadow-md">
                Kumpulkan
            </button>
        </div>
    </div>

    <!-- Main Content Area: List of questions -->
    <div class="max-w-3xl mx-auto w-full px-4 py-6 space-y-6">
        @foreach($ujian->soals as $index => $soal)
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 sm:p-5" id="soal-container-{{ $soal->id }}">
            <div class="flex items-center gap-2 mb-3">
                <span class="w-7 h-7 bg-blue-100 text-blue-800 border border-blue-200 rounded-full text-xs font-black flex items-center justify-center">{{ $index + 1 }}</span>
                <span class="text-xs bg-slate-100 border border-slate-200 text-slate-500 font-bold px-2 py-0.5 rounded uppercase">{{ $soal->tipe }}</span>
                <span class="text-xs text-slate-400 font-medium">Bobot: {{ $soal->bobot }}</span>
            </div>

            <!-- Pertanyaan -->
            <div class="text-slate-800 font-semibold text-sm sm:text-base mb-4 select-text leading-relaxed">
                {!! nl2br(e($soal->pertanyaan)) !!}
            </div>

            <!-- Jawaban Input -->
            @if($soal->tipe === 'pg')
            <!-- Pilihan Ganda -->
            <div class="space-y-2.5">
                @php
                    $valTersimpan = $jawabanTersimpan[$soal->id] ?? null;
                @endphp
                @foreach(['a' => $soal->opsi_a, 'b' => $soal->opsi_b, 'c' => $soal->opsi_c, 'd' => $soal->opsi_d] as $key => $opsiText)
                <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer select-none transition-all active:bg-blue-50" 
                       :class="jawabanState['{{ $soal->id }}'] === '{{ $key }}' ? 'border-blue-500 bg-blue-50/20 font-bold text-blue-900' : 'border-slate-200 hover:bg-slate-50 text-slate-700'">
                    <input type="radio" 
                           name="jawaban_{{ $soal->id }}" 
                           value="{{ $key }}" 
                           @change="saveJawabanPg({{ $soal->id }}, '{{ $key }}')"
                           :checked="jawabanState['{{ $soal->id }}'] === '{{ $key }}'"
                           class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-500 cursor-pointer">
                    <span class="text-xs sm:text-sm font-bold uppercase text-slate-400">{{ $key }}.</span>
                    <span class="text-xs sm:text-sm leading-tight">{{ $opsiText }}</span>
                </label>
                @endforeach
            </div>
            @else
            <!-- Essay / Uraian -->
            <div>
                <textarea rows="4" 
                          placeholder="Ketik jawaban uraian Anda di sini..." 
                          @input="queueSaveEssay({{ $soal->id }}, $event.target.value)"
                          class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-xs sm:text-sm font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                          x-text="jawabanState['{{ $soal->id }}'] || ''">{{ $jawabanTersimpan[$soal->id] ?? '' }}</textarea>
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <!-- Modal: Cheat Warning Overlay -->
    <div x-show="showWarningModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-70 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl p-6 shadow-2xl w-full max-w-sm text-center">
            <div class="w-14 h-14 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4 border border-red-200">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h3 class="font-extrabold text-red-600 text-lg mb-2">Peringatan Kecurangan!</h3>
            <p class="text-sm text-slate-600 mb-4 font-semibold leading-relaxed">
                Anda terdeteksi keluar dari aplikasi/tab ujian! Tindakan ini telah dicatat oleh sistem pengawas ujian.
            </p>
            <div class="bg-red-50 text-red-700 text-xs font-bold py-2 px-3 rounded-lg mb-5 border border-red-100">
                Jumlah Pelanggaran: <span x-text="switchCount">0</span>
            </div>
            <button @click="dismissWarning()" class="w-full bg-red-600 hover:bg-red-700 text-white font-extrabold py-2.5 px-4 rounded-xl shadow-md transition-all active:scale-[0.98] text-sm">
                Lanjutkan Ujian
            </button>
        </div>
    </div>

    <!-- Modal: Submit Confirmation -->
    <div x-show="showConfirmModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl p-6 shadow-2xl w-full max-w-sm text-center">
            <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 border border-blue-200">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h3 class="font-extrabold text-slate-800 text-lg mb-2">Kumpulkan Ujian?</h3>
            <p class="text-sm text-slate-600 mb-5 leading-relaxed font-semibold">
                Apakah Anda yakin ingin menyelesaikan dan mengumpulkan hasil ujian ini? Jawaban tidak dapat diubah lagi setelah dikirim.
            </p>
            <div class="flex gap-2">
                <button @click="showConfirmModal = false" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2.5 px-4 rounded-xl transition-all active:scale-[0.98] text-sm">
                    Batal
                </button>
                <button @click="submitUjian('manual')" 
                        :disabled="isSubmitting"
                        :class="{'opacity-50 cursor-not-allowed': isSubmitting, 'hover:bg-red-700': !isSubmitting}"
                        class="flex-1 bg-red-600 text-white font-extrabold py-2.5 px-4 rounded-xl shadow-md transition-all active:scale-[0.98] text-sm">
                    <span x-show="!isSubmitting">Kumpulkan</span>
                    <span x-show="isSubmitting">Loading...</span>
                </button>
            </div>
        </div>
    </div>

</div>

<!-- AJAX and Anti-cheat Script -->
<script>
    function ujianState(sisaDetik, ujianId, siswaId) {
        return {
            sisaWaktu: sisaDetik,
            ujianId: ujianId,
            siswaId: siswaId,
            timerText: '00:00:00',
            saveStatus: 'saved',
            saveStatusText: 'Semua jawaban tersimpan',
            showWarningModal: false,
            showConfirmModal: false,
            switchCount: 0,
            timerInterval: null,
            jawabanState: {},
            isSubmitting: false,
            isDirty: false,
            isSaving: false,

            localStorageKey() {
                return 'ujian_answers_' + this.ujianId + '_' + this.siswaId;
            },

            initUjian() {
                // Initialize local state of answers from Blade values
                @foreach($ujian->soals as $soal)
                    @if(isset($jawabanTersimpan[$soal->id]))
                        this.jawabanState['{{ $soal->id }}'] = '{{ $jawabanTersimpan[$soal->id] }}';
                    @endif
                @endforeach

                // Restore from LocalStorage if it has newer/unsynced data
                this.restoreFromLocalStorage();

                // Anti cheat listener
                this.setupAntiCheat();

                // Start timer
                this.startTimer();

                // Start Batched Autosave
                setInterval(() => {
                    if (this.isDirty && !this.isSaving && !this.isSubmitting) {
                        this.kirimBatchAutosave();
                    }
                }, 15000);
            },

            restoreFromLocalStorage() {
                try {
                    const localDataStr = localStorage.getItem(this.localStorageKey());
                    if (localDataStr) {
                        const localData = JSON.parse(localDataStr);
                        if (localData && localData.ujian_id === this.ujianId && localData.siswa_id === this.siswaId) {
                            if (localData.answers && Object.keys(localData.answers).length > 0) {
                                // Merge answers over server values safely
                                Object.assign(this.jawabanState, localData.answers);
                                this.isDirty = true; // Mark dirty so it syncs to server in the next batch
                            }
                        } else {
                            // Data mismatch, remove stale data
                            localStorage.removeItem(this.localStorageKey());
                        }
                    }
                } catch (e) {
                    console.error('Failed to parse local storage', e);
                }
            },

            saveToLocalStorage() {
                try {
                    localStorage.setItem(this.localStorageKey(), JSON.stringify({
                        ujian_id: this.ujianId,
                        siswa_id: this.siswaId,
                        answers: this.jawabanState,
                        updated_at: Date.now()
                    }));
                    this.isDirty = true;
                } catch (e) {
                    console.error('Failed to save to local storage', e);
                }
            },

            setupAntiCheat() {
                let hasLeft = false;

                // Tab visibility change
                document.addEventListener('visibilitychange', () => {
                    if (document.visibilityState === 'hidden') {
                        if (!hasLeft) {
                            hasLeft = true;
                            this.logCheat('tab_switch', 'Siswa berganti tab browser atau meminimalkan aplikasi');
                        }
                    }
                });

                // Window blur
                window.addEventListener('blur', () => {
                    if (!hasLeft) {
                        hasLeft = true;
                        this.logCheat('blur', 'Siswa berpindah fokus ke aplikasi lain');
                    }
                });

                // Window focus
                window.addEventListener('focus', () => {
                    hasLeft = false;
                });
            },

            logCheat(event, detail) {
                fetch('{{ route("siswa.ujian.log") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        ujian_id: this.ujianId,
                        event: event,
                        detail: detail
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.switch_count !== undefined) {
                        this.switchCount = data.switch_count;
                        this.showWarningModal = true;
                    }
                })
                .catch(err => console.error('Gagal mencatat log aktivitas:', err));
            },

            dismissWarning() {
                this.showWarningModal = false;
            },

            // Format timer countdown
            startTimer() {
                const updateDisplay = () => {
                    if (this.sisaWaktu <= 0) {
                        clearInterval(this.timerInterval);
                        this.submitUjian('waktu_habis');
                        return;
                    }
                    this.sisaWaktu--;
                    
                    const jam = Math.floor(this.sisaWaktu / 3600);
                    const menit = Math.floor((this.sisaWaktu % 3600) / 60);
                    const detik = this.sisaWaktu % 60;
                    
                    this.timerText = [
                        jam.toString().padStart(2, '0'),
                        menit.toString().padStart(2, '0'),
                        detik.toString().padStart(2, '0')
                    ].join(':');
                };

                updateDisplay();
                this.timerInterval = setInterval(updateDisplay, 1000);
            },

            // Save multiple choice selection
            saveJawabanPg(soalId, pilihan) {
                this.jawabanState[soalId] = pilihan;
                this.saveStatusText = 'Belum tersinkron';
                this.saveToLocalStorage();
            },

            // Essay inputs (no longer debounce individual requests)
            queueSaveEssay(soalId, text) {
                this.jawabanState[soalId] = text;
                this.saveStatusText = 'Belum tersinkron';
                this.saveToLocalStorage();
            },

            // Batched Autosave
            kirimBatchAutosave() {
                this.isSaving = true;
                this.saveStatus = 'saving';
                this.saveStatusText = 'Menyimpan...';

                fetch(`{{ url('/app/ujian-siswa') }}/${this.ujianId}/simpan-jawaban`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        jawaban: this.jawabanState
                    })
                })
                .then(res => {
                    if (res.status === 409) {
                        return { conflict: true };
                    }
                    if (res.status === 429 || res.status >= 500) {
                        return { retry: true };
                    }
                    if (!res.ok) throw new Error('Network response not ok');
                    return res.json();
                })
                .then(data => {
                    if (data && data.conflict) {
                        this.saveStatus = 'saved';
                        this.saveStatusText = 'Ujian sudah ditutup';
                        // Keep isDirty true so it doesn't try to clear things incorrectly, just redirect manually
                        window.location.href = `{{ url('/app/ujian-siswa') }}/${this.ujianId}/hasil`;
                        return;
                    }
                    
                    if (data && data.retry) {
                        this.saveStatus = 'error';
                        this.saveStatusText = 'Menunggu koneksi...';
                        this.isSaving = false;
                        return;
                    }

                    if (data.success) {
                        this.saveStatus = 'saved';
                        this.saveStatusText = 'Tersimpan di perangkat & server';
                        this.isDirty = false;
                        this.isSaving = false;
                    } else {
                        this.saveStatus = 'error';
                        this.saveStatusText = 'Belum tersinkron — aman di perangkat.';
                        this.isSaving = false;
                    }
                })
                .catch(err => {
                    this.saveStatus = 'error';
                    this.saveStatusText = 'Menunggu koneksi... aman di perangkat.';
                    this.isSaving = false;
                });
            },

            confirmSubmit() {
                this.isSubmitting = false;
                this.showConfirmModal = true;
            },

            submitUjian(reasonType) {
                if (this.isSubmitting) return;
                this.isSubmitting = true;

                let reason = 'Siswa submit manual';
                if (reasonType === 'waktu_habis') {
                    reason = 'Ujian selesai otomatis karena waktu habis';
                }

                // Force final backup locally just in case
                this.saveToLocalStorage();

                // Show submitting loading overlay
                this.saveStatus = 'saving';
                this.saveStatusText = 'Sedang mengirim lembar ujian...';

                fetch(`{{ url('/app/ujian-siswa') }}/${this.ujianId}/submit`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        reason: reason,
                        jawaban: this.jawabanState
                    })
                })
                .then(res => {
                    if (res.status === 419) {
                        alert('Sesi keamanan Anda telah disegarkan oleh server. Halaman akan dimuat ulang secara aman (jawaban tidak hilang).');
                        window.location.reload();
                        return;
                    }
                    if (!res.ok) {
                        throw new Error('HTTP ' + res.status);
                    }
                    return res.json();
                })
                .then(data => {
                    if (!data) return;
                    if (data.success) {
                        // Clean up localStorage because submit is successful
                        localStorage.removeItem(this.localStorageKey());
                        window.location.href = data.redirect;
                    } else {
                        alert('Gagal mengumpulkan ujian: ' + (data.message || 'Terjadi kendala'));
                        this.saveStatus = 'error';
                        this.saveStatusText = 'Gagal mengirim. Jawaban aman di perangkat.';
                        this.isSubmitting = false;
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Terjadi kendala koneksi saat mengumpulkan ujian. Jawaban Anda tersimpan aman di perangkat. Silakan klik Kumpulkan kembali.');
                    this.saveStatus = 'error';
                    this.saveStatusText = 'Menunggu koneksi... aman di perangkat.';
                    this.isSubmitting = false;
                });
            }
        };
    }

    // Anti-bfcache listener: Jika siswa menekan tombol Back lalu kembali lagi, reload instan agar tombol & CSRF segar
    window.addEventListener('pageshow', function(event) {
        if (event.persisted || (window.performance && window.performance.navigation && window.performance.navigation.type === 2)) {
            window.location.reload();
        }
    });

</script>

@endsection
