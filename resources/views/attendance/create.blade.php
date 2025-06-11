<x-layouts.app>
    <!-- Breadcrumbs -->
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('dashboard') }}"
            class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Dashboard') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('attendance.index') }}"
            class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Absensi') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-500 dark:text-gray-400">
            {{ $todayAttendance && $todayAttendance->clock_in ? 'Clock Out' : 'Clock In' }}
        </span>
    </div>

    <!-- Current Time Display -->
    <div class="mb-6 text-center">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-8">
            <div class="text-4xl font-bold text-gray-800 dark:text-gray-100 mb-2"
                 x-data="{
                     time: '',
                     updateTime() {
                         this.time = new Date().toLocaleTimeString('id-ID', {
                             hour: '2-digit',
                             minute: '2-digit',
                             second: '2-digit'
                         });
                     }
                 }"
                 x-init="updateTime(); setInterval(() => updateTime(), 1000)"
                 x-text="time">
            </div>
            <div class="text-lg text-gray-600 dark:text-gray-400">
                {{ now()->translatedFormat('l, d F Y') }}
            </div>
        </div>
    </div>

    <!-- Current Status -->
    @if($todayAttendance)
        <div class="mb-6">
            <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-blue-800 dark:text-blue-200 mb-4">Status Hari Ini</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Clock In Status -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4">
                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Clock In</div>
                        <div class="text-xl font-bold text-green-600">
                            {{ $todayAttendance->clock_in ? $todayAttendance->clock_in->format('H:i:s') : 'Belum Clock In' }}
                        </div>
                        @if($todayAttendance->clock_in_notes)
                            <div class="text-sm text-gray-500 mt-1">{{ $todayAttendance->clock_in_notes }}</div>
                        @endif
                    </div>

                    <!-- Status -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4">
                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Status</div>
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $todayAttendance->status_color }}">
                            {{ $todayAttendance->status_label }}
                        </span>
                    </div>

                    <!-- Work Duration -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4">
                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Durasi Kerja</div>
                        <div class="text-xl font-bold text-blue-600">
                            @if($todayAttendance->clock_in && $todayAttendance->clock_out)
                                {{ $todayAttendance->formatted_work_hours }}
                            @elseif($todayAttendance->clock_in)
                                <span x-data="{
                                    duration: '',
                                    updateDuration() {
                                        const clockIn = new Date('{{ $todayAttendance->clock_in->toISOString() }}');
                                        const now = new Date();
                                        const diff = now - clockIn;
                                        const hours = Math.floor(diff / (1000 * 60 * 60));
                                        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                                        this.duration = hours + ':' + minutes.toString().padStart(2, '0');
                                    }
                                }"
                                x-init="updateDuration(); setInterval(() => updateDuration(), 60000)"
                                x-text="duration">
                                </span>
                            @else
                                0:00
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Clock In/Out Action -->
    <div class="max-w-md mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">

            @if(!$todayAttendance || !$todayAttendance->clock_in)
                <!-- Clock In Form -->
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-2">Clock In</h2>
                    <p class="text-gray-600 dark:text-gray-400">Mulai hari kerja Anda</p>
                </div>

                <form action="{{ route('attendance.clock-in') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <x-forms.input
                            label="Catatan (Opsional)"
                            name="notes"
                            type="text"
                            placeholder="Tambahkan catatan jika diperlukan..."
                            value="{{ old('notes') }}" />
                    </div>

                    <div>
                        <x-forms.input
                            label="Lokasi"
                            name="location"
                            type="text"
                            placeholder="Office"
                            value="{{ old('location', 'Office') }}" />
                    </div>

                    <button type="submit"
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Clock In Sekarang
                    </button>
                </form>

            @elseif($todayAttendance->clock_in && !$todayAttendance->clock_out)
                <!-- Clock Out Form -->
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-2">Clock Out</h2>
                    <p class="text-gray-600 dark:text-gray-400">Akhiri hari kerja Anda</p>
                </div>

                <form action="{{ route('attendance.clock-out') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <x-forms.input
                            label="Catatan (Opsional)"
                            name="notes"
                            type="text"
                            placeholder="Ringkasan aktivitas hari ini..."
                            value="{{ old('notes') }}" />
                    </div>

                    <div>
                        <x-forms.input
                            label="Lokasi"
                            name="location"
                            type="text"
                            placeholder="Office"
                            value="{{ old('location', 'Office') }}" />
                    </div>

                    <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Clock Out Sekarang
                    </button>
                </form>

            @else
                <!-- Already Completed -->
                <div class="text-center">
                    <div class="mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-green-500 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-2">Absensi Selesai</h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        Anda telah menyelesaikan absensi untuk hari ini.
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Total jam kerja: <span class="font-semibold">{{ $todayAttendance->formatted_work_hours }}</span>
                    </p>
                </div>
            @endif

            <!-- Navigation -->
            <div class="mt-6 text-center">
                <a href="{{ route('attendance.index') }}"
                   class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                    ← Kembali ke Daftar Absensi
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Attendance Summary -->
    @if($todayAttendance)
        <div class="mt-8 max-w-4xl mx-auto">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Detail Absensi Hari Ini</h3>

                <div class="space-y-3">
                    @if($todayAttendance->clock_in)
                        <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                            <span class="text-gray-600 dark:text-gray-400">Clock In</span>
                            <span class="font-medium">{{ $todayAttendance->clock_in->format('H:i:s') }}</span>
                        </div>
                    @endif

                    @if($todayAttendance->clock_out)
                        <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                            <span class="text-gray-600 dark:text-gray-400">Clock Out</span>
                            <span class="font-medium">{{ $todayAttendance->clock_out->format('H:i:s') }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Status</span>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $todayAttendance->status_color }}">
                            {{ $todayAttendance->status_label }}
                        </span>
                    </div>

                    @if($todayAttendance->clock_in_notes)
                        <div class="flex justify-between items-start py-2 border-b border-gray-200 dark:border-gray-700">
                            <span class="text-gray-600 dark:text-gray-400">Catatan Clock In</span>
                            <span class="font-medium text-right">{{ $todayAttendance->clock_in_notes }}</span>
                        </div>
                    @endif

                    @if($todayAttendance->clock_out_notes)
                        <div class="flex justify-between items-start py-2">
                            <span class="text-gray-600 dark:text-gray-400">Catatan Clock Out</span>
                            <span class="font-medium text-right">{{ $todayAttendance->clock_out_notes }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</x-layouts.app>
