<?php

namespace App\Policies;

use App\Models\User;
use App\Models\LaporanKerusakan;
use Illuminate\Auth\Access\HandlesAuthorization;

class LaporanKerusakanPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_perawatan::kendaraan');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LaporanKerusakan $laporanKerusakan): bool
    {
        return $user->can('view_perawatan::kendaraan');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_perawatan::kendaraan');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LaporanKerusakan $laporanKerusakan): bool
    {
        return $user->can('update_perawatan::kendaraan');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LaporanKerusakan $laporanKerusakan): bool
    {
        return $user->can('delete_perawatan::kendaraan');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_perawatan::kendaraan');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, LaporanKerusakan $laporanKerusakan): bool
    {
        return $user->can('force_delete_perawatan::kendaraan');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_perawatan::kendaraan');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, LaporanKerusakan $laporanKerusakan): bool
    {
        return $user->can('restore_perawatan::kendaraan');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_perawatan::kendaraan');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, LaporanKerusakan $laporanKerusakan): bool
    {
        return $user->can('replicate_perawatan::kendaraan');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_perawatan::kendaraan');
    }
}
