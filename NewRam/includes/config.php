<?php

/**
 * Load an optional repository-root .env file for local development.
 * Deployment environments should provide these values through the process
 * environment instead.
 */
function bfms_load_local_environment(): void
{
    $envFile = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';
    if (!is_readable($envFile)) {
        return;
    }

    $values = parse_ini_file($envFile, false, INI_SCANNER_RAW);
    if ($values === false) {
        throw new RuntimeException('Unable to parse the local environment file.');
    }

    foreach ($values as $name => $value) {
        if (getenv($name) === false) {
            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
        }
    }
}

function bfms_env(string $name, ?string $default = null): ?string
{
    $value = getenv($name);
    if ($value === false || $value === '') {
        return $default;
    }

    return $value;
}

function bfms_required_env(string $name): string
{
    $value = bfms_env($name);
    if ($value === null) {
        throw new RuntimeException("Missing required environment variable: {$name}");
    }

    return $value;
}

function bfms_is_production(): bool
{
    return strtolower((string) bfms_env('APP_ENV', 'production')) === 'production';
}

function bfms_app_url(string $path = ''): string
{
    $baseUrl = rtrim((string) bfms_env('APP_BASE_URL', 'http://localhost/NewRam'), '/');
    if ($path === '') {
        return $baseUrl;
    }

    return $baseUrl . '/' . ltrim($path, '/');
}

bfms_load_local_environment();

error_reporting(E_ALL);
ini_set('log_errors', '1');
ini_set('display_errors', bfms_is_production() ? '0' : '1');
ini_set('display_startup_errors', bfms_is_production() ? '0' : '1');
