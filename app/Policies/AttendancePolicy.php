<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    /**
     * Determine whether the user can view attendance records
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('kelola absen');
    }

    /**
     * Determine whether the user can view a specific attendance record
     */
    public function view(User $user, Attendance $attendance): bool
    {
        // Users can view their own attendance or managers can view all
        return $user->hasPermissionTo('kelola absen') &&
               ($attendance->user_id === $user->id || $user->hasRole(['Manager']));
    }

    /**
     * Determine whether the user can create attendance records
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('kelola absen');
    }

    /**
     * Determine whether the user can update attendance records
     */
    public function update(User $user, Attendance $attendance): bool
    {
        // Only managers can edit attendance records
        return $user->hasRole(['Manager']) && $user->hasPermissionTo('kelola absen');
    }

    /**
     * Determine whether the user can delete attendance records
     */
    public function delete(User $user, Attendance $attendance): bool
    {
        // Only managers can delete attendance records
        return $user->hasRole(['Manager']) && $user->hasPermissionTo('kelola absen');
    }
}
