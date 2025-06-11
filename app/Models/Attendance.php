<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'clock_in_notes',
        'clock_out_notes',
        'status',
        'work_hours',
        'clock_in_location',
        'clock_out_location',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'work_hours' => 'decimal:2',
    ];

    /**
     * Get the user that owns the attendance record
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate work hours based on clock in and clock out times
     */
    public function calculateWorkHours(): void
    {
        if ($this->clock_in && $this->clock_out) {
            $clockIn = Carbon::parse($this->clock_in);
            $clockOut = Carbon::parse($this->clock_out);

            // Calculate hours with decimal precision
            $this->work_hours = $clockOut->diffInMinutes($clockIn) / 60;

            // Determine status based on work hours and timing
            $this->determineStatus();
        }
    }

    /**
     * Determine attendance status based on timing
     */
    public function determineStatus(): void
    {
        if (!$this->clock_in) {
            $this->status = 'absent';
            return;
        }

        $clockInTime = Carbon::parse($this->clock_in);
        $standardStartTime = Carbon::parse('08:00:00');

        // Consider late if clocked in after 8:15 AM
        if ($clockInTime->gt($standardStartTime->copy()->addMinutes(15))) {
            $this->status = 'late';
        } else {
            $this->status = 'present';
        }
    }

    /**
     * Scope for today's attendance
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }

    /**
     * Scope for current month's attendance
     */
    public function scopeCurrentMonth($query)
    {
        return $query->whereMonth('date', now()->month)
                    ->whereYear('date', now()->year);
    }

    /**
     * Scope for specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Check if user can clock out
     */
    public function canClockOut(): bool
    {
        return $this->clock_in && !$this->clock_out;
    }

    /**
     * Check if attendance is complete
     */
    public function isComplete(): bool
    {
        return $this->clock_in && $this->clock_out;
    }

    /**
     * Get formatted work hours
     */
    public function getFormattedWorkHoursAttribute(): string
    {
        if (!$this->work_hours) {
            return '0:00';
        }

        $hours = floor($this->work_hours);
        $minutes = ($this->work_hours - $hours) * 60;

        return sprintf('%d:%02d', $hours, $minutes);
    }

    /**
     * Get status label with proper formatting
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'absent' => 'Tidak Hadir',
            'sick' => 'Sakit',
            'permission' => 'Izin',
            default => 'Unknown'
        };
    }

    /**
     * Get status color for UI display
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'present' => 'text-green-600 bg-green-100',
            'late' => 'text-yellow-600 bg-yellow-100',
            'absent' => 'text-red-600 bg-red-100',
            'sick' => 'text-blue-600 bg-blue-100',
            'permission' => 'text-purple-600 bg-purple-100',
            default => 'text-gray-600 bg-gray-100'
        };
    }
}
