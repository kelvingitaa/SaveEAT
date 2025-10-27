<?php
namespace App\Core;

class Validator
{
    public static function required(string $value): bool { return trim($value) !== ''; }
    public static function email(string $value): bool { return filter_var($value, FILTER_VALIDATE_EMAIL) !== false; }
    public static function min(string $value, int $len): bool { return mb_strlen($value) >= $len; }
    public static function number($value): bool { return is_numeric($value); }
}
