<?php
// config/app.php

define('APP_NAME', 'ClinicPro');
define('APP_VERSION', '1.0.0');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost/clinicpro/public');
define('APP_ENV', getenv('APP_ENV') ?: 'production');
define('APP_DEBUG', APP_ENV === 'development');
define('APP_KEY', getenv('APP_KEY') ?: 'base64:ClinicProSecretKey2024XYZ!@#$%^&*');
define('APP_TIMEZONE', 'Asia/Kolkata');
define('APP_CURRENCY', '₹');
define('APP_CURRENCY_CODE', 'INR');

// Paths
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('VIEW_PATH', BASE_PATH . '/views');
define('CONFIG_PATH', BASE_PATH . '/config');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('UPLOAD_PATH', BASE_PATH . '/public/uploads');
define('PUBLIC_PATH', BASE_PATH . '/public');

// Session
define('SESSION_NAME', 'clinicpro_session');
define('SESSION_LIFETIME', 7200); // 2 hours

// Pagination
define('PER_PAGE', 25);

// Upload limits
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_DOC_TYPES', ['application/pdf', 'image/jpeg', 'image/png']);

// License server
define('LICENSE_SERVER', 'https://license.clinicpro.in/api/verify');

date_default_timezone_set(APP_TIMEZONE);
