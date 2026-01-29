<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user = trim($_POST['user'] ?? '');
  $pass = (string)($_POST['pass'] ?? '');

  if ($user === ADMIN_USER && password_verify($pass, ADMIN_PASSWORD_HASH)) {
    $_SESSION['is_admin'] = true;
    csrfToken();
    header('Location: /gaestebuch.php');
    exit;
  }

  $error = 'Login fehlgeschlagen.';
}
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Login – räumungsmax.at</title>
  <link rel="stylesheet" href="/css/styles.css" />
</head>
<body>
  <div class="app" style="grid-template-columns:1fr;">
    <main class="content" style="max-width:720px;margin:0 auto;">
      <section class="page-hero">
        <div class="page-hero__copy">
          <div class="kicker">Admin</div>
          <h1>Bewertungen verwalten</h1>
          <p class="lead">Nur für Horst Schön</p>
        </div>
      </section>

      <section class="section">
        <article class="card gb-card">
          <?php if ($error): ?>
            <p class="muted" style="margin-top:0;color:#b91c1c;"><?php echo htmlspecialchars($error); ?></p>
          <?php endif; ?>

          <form method="post" class="gb-form">
            <div class="gb-field">
              <span class="gb-icon" aria-hidden="true">👤</span>
              <input type="text" name="user" placeholder="Benutzer" required>
            </div>

            <div class="gb-field">
              <span class="gb-icon" aria-hidden="true">🔒</span>
              <input type="password" name="pass" placeholder="Passwort" required>
            </div>

            <button class="btn btn--primary gb-submit" type="submit">Login</button>
          </form>
        </article>
      </section>
    </main>
  </div>
</body>
</html>