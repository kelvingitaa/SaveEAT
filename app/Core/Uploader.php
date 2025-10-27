<?php
namespace App\Core;

class Uploader
{
    public static function image(array $file): ?string
    {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, ALLOWED_IMAGE_MIME)) {
            return null;
        }
        if ($file['size'] > MAX_UPLOAD_BYTES) {
            return null;
        }
        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0777, true);
        }
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'jpg';
        $name = bin2hex(random_bytes(8)) . '.' . strtolower($ext);
        $dest = rtrim(UPLOAD_DIR, '/') . '/' . $name;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return 'uploads/' . $name;
        }
        return null;
    }
}
