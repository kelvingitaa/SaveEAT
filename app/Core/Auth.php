<?php
namespace App\Core;

class Auth
{
    public static function user(): ?array
    {
        return Session::get('user');
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        $u = self::user();
        return $u['id'] ?? null;
    }

    public static function attempt(array $user): void
    {
        Session::set('user', $user);
        session_regenerate_id(true);
    }

    public static function logout(): void
    {
        Session::destroy();
    }

    public static function requireRole(array $roles): void
    {
        $u = self::user();
        if (!$u || !in_array($u['role'], $roles)) {
            header('HTTP/1.1 403 Forbidden');
            echo View::render('errors/403');
            exit;
        }
    }
}
