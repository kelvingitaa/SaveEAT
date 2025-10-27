<?php
namespace App\Core;

class CSRF
{
    public static function token(): string
    {
        $key = defined('CSRF_TOKEN_KEY') ? CSRF_TOKEN_KEY : '_csrf';
        $token = bin2hex(random_bytes(16));
        Session::set($key, $token);
        return $token;
    }

    public static function check(string $token): bool
    {
        $key = defined('CSRF_TOKEN_KEY') ? CSRF_TOKEN_KEY : '_csrf';
        $valid = hash_equals((string)Session::get($key), (string)$token);
        if ($valid) {
            Session::set($key, null);
        }
        return $valid;
    }
}
