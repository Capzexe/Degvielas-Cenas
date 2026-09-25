<?php

namespace App\Enums;

enum Role: string
{
    case Customer = 'customer';
    case Staff = 'staff';
    case Admin = 'admin';

    public function isStaff(): bool
    {
        return in_array($this, [self::Staff, self::Admin], true);
    }
}
