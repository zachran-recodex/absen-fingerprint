<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * Display attendance dashboard with today's status and recent records
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $currentMonth = now()->format('Y-m');

        // Get filter parameters
        $month = $request->get('month', $currentMonth);
        $userId = $request->get('user_id', $user->id);

        // For managers, allow viewing all users' attendance
        $canViewAllUsers = $user->can('kelola gaji') || $user->hasRole('Manager');

        if (!$canViewAllUsers) {
            $userId = $user->id; // Force to own attendance for non-managers
        }

        // Today's attendance
        $todayAttendance = Attendance::where('user_id', $userId)
            ->whereDate('date', today())
            ->first();

        // Monthly attendance records
        $attendances = Attendance::with('user')
            ->when($userId, fn($query) => $query->where('user_id', $userId))
            ->whereYear('date', Carbon::parse($month)->year)
            ->whereMonth('date', Carbon::parse($month)->month)
            ->orderBy('date', 'desc')
            ->paginate(20);

        // Monthly statistics
        $monthlyStats = $this->getMonthlyStats($userId, $month);

        // Get users list for managers
        $users = $canViewAllUsers ? \App\Models\User::orderBy('name')->get() : collect([$user]);

        return view('attendance.index', compact(
            'todayAttendance',
            'attendances',
            'monthlyStats',
            'users',
            'userId',
            'month',
            'canViewAllUsers'
        ));
    }

    /**
     * Show clock in/out form
     */
    public function create(): View
    {
        $user = Auth::user();
        $today = today();

        // Check if already has attendance record for today
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        return view('attendance.create', compact('todayAttendance'));
    }

    /**
     * Handle clock in action
     */
    public function clockIn(Request $request): RedirectResponse
    {
        $request->validate([
            'notes' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $today = today();

        // Check if already clocked in today
        $existingAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($existingAttendance && $existingAttendance->clock_in) {
            return redirect()->route('attendance.index')
                ->with('error', 'Anda sudah melakukan clock in hari ini.');
        }

        // Create or update attendance record
        $attendance = $existingAttendance ?? new Attendance([
            'user_id' => $user->id,
            'date' => $today,
        ]);

        $attendance->clock_in = now();
        $attendance->clock_in_notes = $request->notes;
        $attendance->clock_in_location = $request->location ?? 'Office';

        // Determine initial status
        $attendance->determineStatus();
        $attendance->save();

        return redirect()->route('attendance.index')
            ->with('status', 'Clock in berhasil! Selamat bekerja.');
    }

    /**
     * Handle clock out action
     */
    public function clockOut(Request $request): RedirectResponse
    {
        $request->validate([
            'notes' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $today = today();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (!$attendance || !$attendance->clock_in) {
            return redirect()->route('attendance.index')
                ->with('error', 'Anda belum melakukan clock in hari ini.');
        }

        if ($attendance->clock_out) {
            return redirect()->route('attendance.index')
                ->with('error', 'Anda sudah melakukan clock out hari ini.');
        }

        // Update attendance record
        $attendance->clock_out = now();
        $attendance->clock_out_notes = $request->notes;
        $attendance->clock_out_location = $request->location ?? 'Office';

        // Calculate work hours
        $attendance->calculateWorkHours();
        $attendance->save();

        return redirect()->route('attendance.index')
            ->with('status', 'Clock out berhasil! Terima kasih atas kerja keras Anda hari ini.');
    }

    /**
     * Display specific attendance record
     */
    public function show(Attendance $attendance): View
    {
        $user = Auth::user();

        // Check permission to view this attendance
        if ($attendance->user_id !== $user->id && !$user->can('kelola gaji')) {
            abort(403, 'Unauthorized');
        }

        $attendance->load('user');

        return view('attendance.show', compact('attendance'));
    }

    /**
     * Show edit form for attendance record (admin only)
     */
    public function edit(Attendance $attendance): View
    {
        $this->authorize('update', $attendance);

        return view('attendance.edit', compact('attendance'));
    }

    /**
     * Update attendance record (admin only)
     */
    public function update(Request $request, Attendance $attendance): RedirectResponse
    {
        $this->authorize('update', $attendance);

        $request->validate([
            'clock_in' => 'required|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i|after:clock_in',
            'status' => 'required|in:present,late,absent,sick,permission',
            'clock_in_notes' => 'nullable|string|max:255',
            'clock_out_notes' => 'nullable|string|max:255',
        ]);

        $attendance->update([
            'clock_in' => $request->clock_in ?
                Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $request->clock_in) : null,
            'clock_out' => $request->clock_out ?
                Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $request->clock_out) : null,
            'status' => $request->status,
            'clock_in_notes' => $request->clock_in_notes,
            'clock_out_notes' => $request->clock_out_notes,
        ]);

        // Recalculate work hours
        $attendance->calculateWorkHours();
        $attendance->save();

        return redirect()->route('attendance.index')
            ->with('status', 'Data absensi berhasil diperbarui.');
    }

    /**
     * Generate attendance report
     */
    public function report(Request $request): View
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $query = Attendance::with('user')
            ->whereBetween('date', [$request->start_date, $request->end_date]);

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $attendances = $query->orderBy('date', 'desc')->get();

        // Calculate summary statistics
        $summary = [
            'total_records' => $attendances->count(),
            'present_count' => $attendances->where('status', 'present')->count(),
            'late_count' => $attendances->where('status', 'late')->count(),
            'absent_count' => $attendances->where('status', 'absent')->count(),
            'total_work_hours' => $attendances->sum('work_hours'),
            'average_work_hours' => $attendances->avg('work_hours'),
        ];

        return view('attendance.report', compact('attendances', 'summary', 'request'));
    }

    /**
     * Get monthly statistics for a user
     */
    private function getMonthlyStats(int $userId, string $month): array
    {
        $attendances = Attendance::where('user_id', $userId)
            ->whereYear('date', Carbon::parse($month)->year)
            ->whereMonth('date', Carbon::parse($month)->month)
            ->get();

        return [
            'total_days' => $attendances->count(),
            'present_days' => $attendances->where('status', 'present')->count(),
            'late_days' => $attendances->where('status', 'late')->count(),
            'absent_days' => $attendances->where('status', 'absent')->count(),
            'total_work_hours' => $attendances->sum('work_hours'),
            'average_work_hours' => $attendances->avg('work_hours') ?? 0,
        ];
    }
}
