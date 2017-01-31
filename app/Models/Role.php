<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Class Role
 */
final class Role
{
    const ROLE_ADMIN = 'Admin';
    const ROLE_USER  = 'User';

    public static $rules =
        [
            self::ROLE_ADMIN,
            self::ROLE_USER,
        ];
}
