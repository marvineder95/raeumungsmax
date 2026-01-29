<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

$_SESSION = [];
session_destroy();

header('Location: /gaestebuch.php');
exit;