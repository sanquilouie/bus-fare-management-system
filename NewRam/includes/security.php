<?php

require_once __DIR__ . '/config.php';

function bfms_start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function bfms_json_error(string $message, int $status): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

function bfms_require_roles(array $allowedRoles): void
{
    bfms_start_secure_session();

    if (empty($_SESSION['id']) || empty($_SESSION['email'])) {
        bfms_json_error('Authentication required.', 401);
    }

    $role = $_SESSION['role'] ?? '';
    if (!in_array($role, $allowedRoles, true)) {
        bfms_json_error('You are not authorized to perform this action.', 403);
    }
}

function bfms_require_authenticated(): void
{
    bfms_start_secure_session();
    if (empty($_SESSION['id']) || empty($_SESSION['email'])) {
        bfms_json_error('Authentication required.', 401);
    }
}

function bfms_require_same_origin(): void
{
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
        return;
    }

    $fetchSite = strtolower($_SERVER['HTTP_SEC_FETCH_SITE'] ?? '');
    if ($fetchSite === 'cross-site') {
        bfms_json_error('Cross-site request rejected.', 403);
    }

    $source = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';
    if ($source === '') {
        if ($fetchSite === '' && bfms_is_production()) {
            bfms_json_error('Unable to verify request origin.', 403);
        }
        return;
    }

    $sourceHost = parse_url($source, PHP_URL_HOST);
    $requestHost = preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? '');
    if (!$sourceHost || !hash_equals(strtolower($requestHost), strtolower($sourceHost))) {
        bfms_json_error('Cross-site request rejected.', 403);
    }
}

function bfms_csrf_token(): string
{
    bfms_start_secure_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function bfms_require_csrf_token(): void
{
    bfms_start_secure_session();
    $provided = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
    $expected = $_SESSION['csrf_token'] ?? '';

    if ($provided === '' || $expected === '' || !hash_equals($expected, $provided)) {
        bfms_json_error('Invalid or missing CSRF token.', 403);
    }
}

function bfms_establish_user_session(array $user): void
{
    bfms_start_secure_session();
    session_regenerate_id(true);

    $sessionFields = ['id', 'firstname', 'lastname', 'email', 'account_number', 'role'];
    foreach ($sessionFields as $field) {
        if (array_key_exists($field, $user)) {
            $_SESSION[$field] = $user[$field];
        }
    }
}
