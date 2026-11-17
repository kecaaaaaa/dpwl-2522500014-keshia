<?php
// P9 BARU - application/helpers/csrf_helper.php
// Token CSRF sederhana berbasis sesi untuk formulir POST pembayaran.
function csrf_token(): string
{
    if (empty($_SESSION['_csrf']) || !is_string($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_valid($token): bool
{
    $sesi = $_SESSION['_csrf'] ?? '';
    return is_string($token)
        && is_string($sesi)
        && $sesi !== ''
        && hash_equals($sesi, $token);
}
