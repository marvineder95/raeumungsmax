<?php
declare(strict_types=1);

/**
 * Guestbook: Add Entry
 * - Spam-Schutz (Honeypot + Zeit)
 * - Rate Limit (pro IP)
 * - Validierung
 * - Speichern mit ID
 */

// =======================
// Konfiguration
// =======================
$dataFile = __DIR__ . '/../data/guestbook.json';
$minFormSeconds = 3;     // Mindestzeit zum Ausfüllen
$rateLimitSeconds = 30; // 1 Eintrag pro IP / 30 Sekunden

// =======================
// Nur POST erlauben
// =======================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /gaestebuch.php');
    exit;
}

// =======================
// Spam-Schutz: Honeypot
// =======================
if (!empty($_POST['website'] ?? '')) {
    http_response_code(400);
    exit('Spam detected.');
}

// =======================
// Spam-Schutz: Zeitprüfung
// =======================
$formStarted = (int)($_POST['form_started'] ?? 0);
if ($formStarted <= 0 || (time() - $formStarted) < $minFormSeconds) {
    http_response_code(400);
    exit('Form submitted too fast.');
}

// =======================
// Rate Limit pro IP
// =======================
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rateFile = __DIR__ . '/../data/ratelimit_' . preg_replace('/[^a-zA-Z0-9\.\-_]/', '_', $ip) . '.txt';

$now = time();
$last = file_exists($rateFile) ? (int)trim((string)@file_get_contents($rateFile)) : 0;

if ($last > 0 && ($now - $last) < $rateLimitSeconds) {
    http_response_code(429);
    exit('Bitte kurz warten und erneut versuchen.');
}

@file_put_contents($rateFile, (string)$now, LOCK_EX);

// =======================
// Eingaben holen
// =======================
$name = trim($_POST['name'] ?? '');
$message = trim($_POST['message'] ?? '');
$rating = (int)($_POST['rating'] ?? 0);

// =======================
// Validierung
// =======================
if ($rating < 1 || $rating > 5) {
    http_response_code(400);
    exit('Ungültige Bewertung.');
}

if (mb_strlen($message) < 10) {
    http_response_code(400);
    exit('Bitte mindestens 10 Zeichen schreiben.');
}

// Begrenzen
if (mb_strlen($name) > 60) {
    $name = mb_substr($name, 0, 60);
}
if (mb_strlen($message) > 1000) {
    $message = mb_substr($message, 0, 1000);
}

// =======================
// XSS-Schutz (beim Speichern)
// =======================
$nameSafe = $name !== '' ? htmlspecialchars($name, ENT_QUOTES, 'UTF-8') : 'Anonym';
$messageSafe = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

// =======================
// Bestehende Einträge laden
// =======================
$entries = [];
if (file_exists($dataFile)) {
    $json = file_get_contents($dataFile);
    $decoded = json_decode($json ?: '[]', true);
    if (is_array($decoded)) {
        $entries = $decoded;
    }
}

// =======================
// NEUER EINTRAG (MIT ID)
// =======================
$entries[] = [
    'id'        => bin2hex(random_bytes(16)), // <-- DAS ist die eindeutige ID
    'name'      => $nameSafe,
    'rating'    => $rating,
    'message'   => $messageSafe,
    'createdAt' => date('c')
];

// =======================
// Speichern
// =======================
file_put_contents(
    $dataFile,
    json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
    LOCK_EX
);

// =======================
// Zurück zum Gästebuch
// =======================
header('Location: /gaestebuch.php');
exit;