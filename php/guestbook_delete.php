<?php
declare(strict_types=1);
require_once __DIR__ . '/../admin/auth.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: /gaestebuch.php');
  exit;
}

$id = trim($_POST['id'] ?? '');
$token = (string)($_POST['csrf'] ?? '');

if ($id === '' || !verifyCsrf($token)) {
  http_response_code(400);
  exit('Bad request');
}

$dataFile = __DIR__ . '/../data/guestbook.json';
$entries = [];

if (file_exists($dataFile)) {
  $entries = json_decode(file_get_contents($dataFile) ?: '[]', true) ?: [];
}

$entries = array_values(array_filter($entries, function ($e) use ($id) {
  return ($e['id'] ?? '') !== $id;
}));

file_put_contents($dataFile, json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);

header('Location: /gaestebuch.php');
exit;