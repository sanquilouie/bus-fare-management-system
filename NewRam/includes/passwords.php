<?php

function bfms_hash_password(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT);
}

function bfms_password_column_supports_modern_hash(mysqli $conn): bool
{
    $result = $conn->query("SHOW COLUMNS FROM useracc LIKE 'password'");
    if (!$result || !($column = $result->fetch_assoc())) {
        return false;
    }

    $type = strtolower((string) ($column['Type'] ?? ''));
    if (preg_match('/^(tinytext|text|mediumtext|longtext)/', $type) === 1) {
        return true;
    }

    return preg_match('/^(?:var)?char\((\d+)\)/', $type, $matches) === 1
        && (int) $matches[1] >= 255;
}

function bfms_hash_password_for_database(mysqli $conn, string $password): string
{
    if (!bfms_password_column_supports_modern_hash($conn)) {
        throw new RuntimeException('The password column must support modern password hashes before passwords can be changed.');
    }

    return bfms_hash_password($password);
}

/**
 * Verify modern hashes and transparently accept legacy MD5 hashes once.
 * The caller should persist $upgradedHash after a successful legacy login.
 */
function bfms_verify_password(string $plainText, string $storedHash, ?string &$upgradedHash = null): bool
{
    $upgradedHash = null;
    $info = password_get_info($storedHash);

    if (!empty($info['algo'])) {
        $valid = password_verify($plainText, $storedHash);
        if ($valid && password_needs_rehash($storedHash, PASSWORD_DEFAULT)) {
            $upgradedHash = bfms_hash_password($plainText);
        }
        return $valid;
    }

    if (preg_match('/^[a-f0-9]{32}$/i', $storedHash) === 1
        && hash_equals(strtolower($storedHash), md5($plainText))) {
        $upgradedHash = bfms_hash_password($plainText);
        return true;
    }

    return false;
}

function bfms_generate_temporary_password(): string
{
    return bin2hex(random_bytes(8)) . '!aA1';
}
