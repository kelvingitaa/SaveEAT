<?php
// Copy to app.php and adjust
const APP_NAME = 'SaveEat';
const BASE_URL = 'http://localhost/SaveEAT/public'; // adjust to your setup
const APP_ENV = 'local';
const APP_DEBUG = true;

// Security
const CSRF_TOKEN_KEY = '_csrf';
const SESSION_NAME = 'saveeat_session';

// File uploads
const UPLOAD_DIR = __DIR__ . '/../public/uploads';
const MAX_UPLOAD_BYTES = 2 * 1024 * 1024; // 2MB
const ALLOWED_IMAGE_MIME = ['image/jpeg','image/png','image/webp'];
