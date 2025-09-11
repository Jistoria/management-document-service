<?php

namespace App\Kafka;

final class Topics
{
    public const PERMISSION_GRANTED = 'auth.rbac.permission-granted';
    public const PERMISSION_REVOKED = 'auth.rbac.permission-revoked';
    public const USER_UPDATED       = 'auth.user.updated';
    public const USER_DELETED       = 'auth.user.deleted';
    public const USER_RESTORED      = 'auth.user.restored';

    public static function all(): array
    {
        return [
            self::PERMISSION_GRANTED,
            self::PERMISSION_REVOKED,
            self::USER_UPDATED,
            self::USER_DELETED,
            self::USER_RESTORED,
        ];
    }
}
