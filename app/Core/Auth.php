<?php
namespace App\Core;

use App\Models\User;

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

    public static function attempt(array $user, bool $remember = false): void
    {
        Session::set('user', $user);
        
        // Set email verification flag in database for persistent storage
        if ($user['role'] === 'consumer') {
            $userModel = new User();
            $userModel->setEmailVerified($user['id'], true);
        }
        
        session_regenerate_id(true);

        // Handle remember me functionality
        if ($remember) {
            self::setRememberToken($user['id']);
        } else {
            // Clear any existing remember token
            self::clearRememberToken();
        }
    }

    public static function logout(): void
    {
        // Clear remember token from database and cookie
        self::clearRememberToken();
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

    public static function isEmailVerified(): bool
    {
        $user = self::user();
        if (!$user || $user['role'] !== 'consumer') {
            return true; // Non-consumers don't need email verification
        }

        $userModel = new User();
        return $userModel->isEmailVerified($user['id']);
    }

    public static function tryRememberLogin(): bool
    {
        // Check if user is already logged in
        if (self::check()) {
            return true;
        }

        // Check for remember token cookie
        $rememberToken = $_COOKIE['remember_token'] ?? null;
        if (!$rememberToken) {
            return false;
        }

        // Validate token and log user in
        $userModel = new User();
        $user = $userModel->findByRememberToken($rememberToken);
        
        if ($user) {
            // Log the user in
            Session::set('user', $user);
            
            session_regenerate_id(true);
            
            // Extend the remember token
            self::setRememberToken($user['id']);
            
            return true;
        } else {
            // Invalid token, clear the cookie
            self::clearRememberToken();
            return false;
        }
    }

    private static function setRememberToken(int $userId): void
    {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60)); // 30 days
        
        $userModel = new User();
        $userModel->updateRememberToken($userId, $token, $expires);
        
        // Set cookie
        setcookie('remember_token', $token, [
            'expires' => time() + (30 * 24 * 60 * 60),
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    private static function clearRememberToken(): void
    {
        // Clear from database if user is logged in
        if (self::check()) {
            $userModel = new User();
            $userModel->updateRememberToken(self::id(), null, null);
        }
        
        // Clear cookie
        setcookie('remember_token', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
}