<?php

namespace App\Helpers;

use App\Models\User;

class RoleRedirect
{
    /**
     * Kembalikan nama route dashboard sesuai role user.
     */
    public static function dashboardRoute(User $user): string
    {
        return match ($user->role) {
            'super_admin' => route('dashboard.superadmin'),
            'admin'       => route('dashboard.admin'),
            'desain'      => route('dashboard.desain'),
            'cetak'       => route('dashboard.cetak'),
            default       => route('login'),
        };
    }
}
