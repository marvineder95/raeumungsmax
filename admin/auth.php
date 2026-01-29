<?php
declare(strict_types=1);

session_start();

const ADMIN_USER = 'horst';
const ADMIN_PASSWORD_HASH = '$2y$12$E7m4XKhElLBs1yAeBpyanOiOYwk3j.NPcAEbHY8J/sqAE.tCxcZgW'; // gleich setzen

function isAdmin(): bool {
  return !empty($_SESSION['is_admin']);
}

function requireAdmin(): void {
  if (!isAdmin()) {
    header('Location: /admin/login.php');
    exit;
  }
}

function csrfToken(): string {
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf'];
}

function verifyCsrf(string $token): bool {
  return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}