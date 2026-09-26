<?php

namespace App\Enums;

enum UserRole: string
{
    case None = 'none';
    case Owner = 'owner';
    case Administrator = 'administrator';
    case Editor = 'editor';
    case InquiryAgent = 'inquiry_agent';

    public function canAccessAdmin(): bool
    {
        return $this !== self::None;
    }

    public function canManageAdminUsers(): bool
    {
        return $this === self::Owner;
    }

    public function canPublishContent(): bool
    {
        return in_array($this, [self::Owner, self::Administrator, self::Editor], true);
    }

    public function canManageInquiries(): bool
    {
        return in_array($this, [self::Owner, self::Administrator, self::InquiryAgent], true);
    }
}
