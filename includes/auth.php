<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function is_admin(): bool {
    return is_logged_in() && ($_SESSION['role'] ?? '') === 'admin';
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function require_admin(): void {
    if (!is_admin()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function current_user_id(): ?int {
    return $_SESSION['user_id'] ?? null;
}

function current_user_name(): string {
    return $_SESSION['user_name'] ?? 'Guest';
}

function flash(string $key, ?string $msg = null): ?string {
    if ($msg !== null) {
        $_SESSION['flash'][$key] = $msg;
        return null;
    }
    $val = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $val;
}

function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
