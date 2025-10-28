<?php
// Dynamic BASE_URL that works automatically
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base_path = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
define('BASE_URL', $protocol . '://' . $host . $base_path);

// App configuration
const APP_NAME = 'SaveEAT';
const APP_ENV = 'local';
const APP_DEBUG = true;

// Security
const CSRF_TOKEN_KEY = '_csrf';
const SESSION_NAME = 'saveeat_session';

// File uploads
define('UPLOAD_DIR', __DIR__ . '/../public/uploads');
const MAX_UPLOAD_BYTES = 2 * 1024 * 1024; // 2MB
const ALLOWED_IMAGE_MIME = ['image/jpeg','image/png','image/webp'];