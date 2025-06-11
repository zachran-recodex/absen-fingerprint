<x-layouts.app>
    <!-- Breadcrumbs -->
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('dashboard') }}"
            class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Dashboard') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-500 dark:text-gray-400">{{ __('Absensi') }}</span>
    </div>

    <!-- Page Title -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Absensi') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Kelola data kehadiran karyawan') }}</p>
        </div>

        @if(!$todayAttendance || !$todayAttendance->clock_out)
            <a href="{{ route('attendance.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
                {{ $todayAttendance && $todayAttendance->clock_in ? 'Clock Out' : 'Clock In' }}
            </a>
        @endif
    </div>

    <!-- Today's Status Card -->
    <div class="mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Status Hari Ini</h2>

            @if($todayAttendance)
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Clock In Status -->
                    <div class="text-center p-4 bg-green-50 dark:bg-green-900 rounded-lg">
                        <div class="text-green-600 dark:text-green-400 font-semibold">Clock In</div>
                        <div class="text-lg font-bold text-green-800 dark:text-green-200">
                            {{ $todayAttendance->clock_in ? $todayAttendance->clock_in->format('H:i') : '-' }}
                        </div>
                    </div>

                    <!-- Clock Out Status -->
                    <div class="text-center p-4 bg-blue-50 dark:bg-blue-900 rounded-lg">
                        <div class="text-blue-600 dark:text-blue-400 font-semibold">Clock Out</div>
                        <div class="text-lg font-bold text-blue-800 dark:text-blue-200">
                            {{ $todayAttendance->clock_out ? $todayAttendance->clock_out->format('H:i') : '-' }}
                        </div>
                    </div>

                    <!-- Work Hours -->
                    <div class="text-center p-4 bg-purple-50 dark:bg-purple-900 rounded-lg">
                        <div class="text-purple-600 dark:text-purple-400 font-semibold">Jam Kerja</div>
                        <div class="text-lg font-bold text-purple-800 dark:text-purple-200">
                            {{ $todayAttendance->formatted_work_hours ?? '0:00' }}
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="text-center p-4 rounded-lg {{ $todayAttendance->status_color }}">
                        <div class="font-semibold">Status</div>
                        <div class="text-lg font-bold">{{ $todayAttendance->status_label }}</div>
                    </div>
                </div>
            @else
                <div class="text-center py-8">
                    <div class="text-gray-500 dark:text-gray-400 mb-4">Belum ada data absensi hari ini</div>
                    <a href="{{ route('attendance.create') }}"
                       class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium">
                        Clock In Sekarang
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Monthly Statistics -->
    <div class="mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Statistik Bulan Ini</h2>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $monthlyStats['total_days'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Hari</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $monthlyStats['present_days'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Hadir</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-600">{{ $monthlyStats['late_days'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Terlambat</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">{{ $monthlyStats['absent_days'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Tidak Hadir</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ number_format($monthlyStats['average_work_hours'], 1) }}h</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Rata-rata Jam</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    @if($canViewAllUsers)
    <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <form method="GET" action="{{ route('attendance.index') }}" class="flex gap-4 items-end">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Karyawan</label>
                <select name="user_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bulan</label>
                <input type="month" name="month" value="{{ $month }}"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                Filter
            </button>
        </form>
    </div>
    @endif

    <!-- Attendance Records -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Riwayat Absensi</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Tanggal
                        </th>
                        @if($canViewAllUsers)
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Karyawan
                        </th>
                        @endif
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Clock In
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Clock Out
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Jam Kerja
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($attendances as $attendance)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $attendance->date->format('d M Y') }}
                            </td>
                            @if($canViewAllUsers)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $attendance->user->name }}
                            </td>
                            @endif
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $attendance->formatted_work_hours ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $attendance->status_color }}">
                                    {{ $attendance->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('attendance.show', $attendance) }}"
                                   class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                    Detail
                                </a>
                                @can('update', $attendance)
                                    <a href="{{ route('attendance.edit', $attendance) }}"
                                       class="ml-3 text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                        Edit
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canViewAllUsers ? '7' : '6' }}" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                Tidak ada data absensi ditemukan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($attendances->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $attendances->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
