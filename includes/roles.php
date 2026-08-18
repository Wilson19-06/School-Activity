<?php

function eams_role(): string
{
    return $_SESSION['role'] ?? '';
}

function eams_user_id(): int
{
    return (int)($_SESSION['user_id'] ?? 0);
}

function eams_is_principal(): bool
{
    return eams_role() === 'principal';
}

function eams_is_teacher(): bool
{
    return in_array(eams_role(), ['teacher', 'admin'], true);
}

function eams_is_staff(): bool
{
    return eams_is_principal() || eams_is_teacher();
}

function eams_can_view_activity(array $activity): bool
{
    if (eams_is_principal()) {
        return true;
    }
    $visibility = $activity['visibility'] ?? 'Public';
    if ($visibility !== 'Private') {
        return true;
    }
    return (int)($activity['created_by'] ?? 0) === eams_user_id();
}

function eams_visibility_sql(string $alias = 'a'): string
{
    if (eams_is_principal()) {
        return '1=1';
    }
    $uid = eams_user_id();
    return "({$alias}.visibility IS NULL OR {$alias}.visibility <> 'Private' OR {$alias}.created_by = {$uid})";
}
