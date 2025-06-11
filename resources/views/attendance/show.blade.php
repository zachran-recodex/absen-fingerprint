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
        <span class="text-gray-500 dark:text-gray-400">Detail</span>
    </div>

    <!-- Page Title -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Detail Absensi</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                {{ $attendance->user->name }} - {{ $attendance->date->translatedFormat('l, d F Y') }}
            </p>
        </div>

        @can('update', $attendance)
            <a href="{{ route('attendance.edit', $attendance) }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
                Edit Absensi
            </a>
        @endcan
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Attendance Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Status Overview -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Status Kehadiran</h2>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- Status Badge -->
                    <div class="text-center">
                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">Status</div>
                        <span class="inline-flex px-3 py-2 text-sm font-semibold rounded-full {{ $attendance->status_color }}">
                            {{ $attendance->status_label }}
                        </span>
                    </div>

                    <!-- Work Hours -->
                    <div class="text-center">
                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">Jam Kerja</div>
                        <div class="text-xl font-bold text-gray-800 dark:text-gray-100">
                            {{ $attendance->formatted_work_hours ?? '0:00' }}
                        </div>
                    </div>

                    <!-- Late Duration (if applicable) -->
                    @if($attendance->status === 'late' && $attendance->clock_in)
                        <div class="text-center">
                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">Keterlambatan</div>
                            <div class="text-xl font-bold text-yellow-600">
                                @php
                                    $standardTime = \Carbon\Carbon::parse($attendance->date->format('Y-m-d') . ' 08:00:00');
                                    $clockIn = \Carbon\Carbon::parse($attendance->clock_in);
                                    $lateDuration = $clockIn->diff($standardTime);
                                @endphp
                                {{ $lateDuration->format('%H:%I') }}
                            </div>
                        </div>
                    @endif

                    <!-- Completion Status -->
                    <div class="text-center">
                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">Kelengkapan</div>
                        <div class="text-sm font-medium">
                            @if($attendance->isComplete())
                                <span class="text-green-600">Lengkap</span>
                            @elseif($attendance->clock_in && !$attendance->clock_out)
                                <span class="text-yellow-600">Belum Clock Out</span>
                            @else
                                <span class="text-red-600">Tidak Lengkap</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Time Details -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Detail Waktu</h2>

                <div class="space-y-4">
                    <!-- Clock In -->
                    <div class="flex items-center justify-between p-4 bg-green-50 dark:bg-green-900 rounded-lg">
                        <div class="flex items-center">
                            <div class="bg-green-100 dark:bg-green-800 p-2 rounded-full mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </div>
                            <div>
                                <div class="font-medium text-gray-800 dark:text-gray-100">Clock In</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $attendance->clock_in_location ?? 'Office' }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-green-600">
                                {{ $attendance->clock_in ? $attendance->clock_in->format('H:i:s') : 'Belum Clock In' }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $attendance->clock_in ? $attendance->clock_in->translatedFormat('d M Y') : '' }}
                            </div>
                        </div>
                    </div>

                    <!-- Clock Out -->
                    <div class="flex items-center justify-between p-4 bg-blue-50 dark:bg-blue-900 rounded-lg">
                        <div class="flex items-center">
                            <div class="bg-blue-100 dark:bg-blue-800 p-2 rounded-full mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </div>
                            <div>
                                <div class="font-medium text-gray-800 dark:text-gray-100">Clock Out</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $attendance->clock_out_location ?? ($attendance->clock_out ? 'Office' : '-') }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-blue-600">
                                {{ $attendance->clock_out ? $attendance->clock_out->format('H:i:s') : 'Belum Clock Out' }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $attendance->clock_out ? $attendance->clock_out->translatedFormat('d M Y') : '' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes Section -->
            @if($attendance->clock_in_notes || $attendance->clock_out_notes)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Catatan</h2>

                    <div class="space-y-4">
                        @if($attendance->clock_in_notes)
                            <div class="border-l-4 border-green-400 pl-4">
                                <div class="text-sm font-medium text-gray-800 dark:text-gray-100">Clock In</div>
                                <div class="text-gray-600 dark:text-gray-400">{{ $attendance->clock_in_notes }}</div>
                            </div>
                        @endif

                        @if($attendance->clock_out_notes)
                            <div class="border-l-4 border-blue-400 pl-4">
                                <div class="text-sm font-medium text-gray-800 dark:text-gray-100">Clock Out</div>
                                <div class="text-gray-600 dark:text-gray-400">{{ $attendance->clock_out_notes }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Employee Info -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Informasi Karyawan</h3>

                <div class="space-y-3">
                    <div class="flex items-center">
                        <span class="relative flex h-10 w-10 shrink-0 overflow-hidden rounded-lg bg-gray-200 text-black dark:bg-gray-700 dark:text-white mr-3">
                            <span class="flex h-full w-full items-center justify-center rounded-lg">
                                {{ $attendance->user->initials() }}
                            </span>
                        </span>
                        <div>
                            <div class="font-medium text-gray-800 dark:text-gray-100">{{ $attendance->user->name }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">NIK: {{ $attendance->user->nik }}</div>
                        </div>
                    </div>

                    @if($attendance->user->roles->isNotEmpty())
                        <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Role</div>
                            <div class="flex flex-wrap gap-1">
                                @foreach($attendance->user->roles as $role)
                                    <span class="inline-flex px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Statistik Bulan Ini</h3>

                @php
                    $monthlyAttendances = \App\Models\Attendance::where('user_id', $attendance->user_id)
                        ->whereMonth('date', $attendance->date->month)
                        ->whereYear('date', $attendance->date->year)
                        ->get();
                @endphp

                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Total Hari</span>
                        <span class="font-medium">{{ $monthlyAttendances->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Hadir</span>
                        <span class="font-medium text-green-600">{{ $monthlyAttendances->where('status', 'present')->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Terlambat</span>
                        <span class="font-medium text-yellow-600">{{ $monthlyAttendances->where('status', 'late')->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Tidak Hadir</span>
                        <span class="font-medium text-red-600">{{ $monthlyAttendances->where('status', 'absent')->count() }}</span>
                    </div>
                    <hr class="border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Total Jam</span>
                        <span class="font-medium">{{ number_format($monthlyAttendances->sum('work_hours'), 1) }}h</span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Aksi</h3>

                <div class="space-y-3">
                    <a href="{{ route('attendance.index') }}"
                       class="block w-full text-center bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-lg font-medium">
                        Kembali ke Daftar
                    </a>

                    @can('update', $attendance)
                        <a href="{{ route('attendance.edit', $attendance) }}"
                           class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
                            Edit Absensi
                        </a>
                    @endcan

                    <!-- Print/Export actions could go here -->
                    <button type="button"
                            onclick="window.print()"
                            class="block w-full text-center bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak Detail
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
