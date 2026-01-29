<?php
declare(strict_types=1);

$DATA_FILE = __DIR__ . '/data/guestbook.json';

require_once __DIR__ . '/admin/auth.php';

// Fallback (falls du e() sonst nirgends hast)
if (!function_exists('e')) {
  function e(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
}

// Reviews laden
$reviews = [];
if (file_exists($DATA_FILE)) {
  $raw = file_get_contents($DATA_FILE);
  $decoded = json_decode($raw ?: '[]', true);
  if (is_array($decoded)) $reviews = $decoded;
}

// IDs nachrüsten (einmalig)
$changed = false;
foreach ($reviews as &$r) {
  if (empty($r['id'])) {
    $r['id'] = bin2hex(random_bytes(16));
    $changed = true;
  }
}
unset($r);

if ($changed) {
  file_put_contents(
    $DATA_FILE,
    json_encode($reviews, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
    LOCK_EX
  );
}

// Neueste zuerst (wenn du unten anhängst)
$reviews = array_reverse($reviews);
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Gästebuch – räumungsmax.at</title>
  <meta name="description" content="Kundenbewertungen und Gästebuch von räumungsmax.at" />
  <link rel="stylesheet" href="./css/styles.css" />
</head>
<body>

<header class="topbar">
  <button class="icon-btn" id="burgerBtn"><span class="burger"></span></button>
  <a class="brand" href="./index.html">
    <img src="./images/logo.png" class="brand__logo" alt="Logo">
    <span class="brand__text">räumungsmax.at</span>
  </a>
</header>

<div class="app">

  <!-- Sidebar -->
  <aside class="sidebar">
    <a class="sidebar__logo" href="./index.html">
      <img src="./images/logo.png" alt="Logo">
    </a>

    <nav class="nav">
      <a class="nav__link" href="./index.html">Home</a>
      <a class="nav__link" href="./entruempelung.html">Entrümpelung</a>
      <a class="nav__link" href="./raeumungen.html">Räumungen</a>
      <a class="nav__link" href="./uebersiedlungen.html">Übersiedlungen</a>
      <a class="nav__link" href="./verlassenschaft.html">Verlassenschaft</a>
      <a class="nav__link" href="./wohnungsraeumungen.html">Wohnungsräumungen</a>
      <a class="nav__link" href="./ankauf.html">Ankauf</a>
      <a class="nav__link" href="./kontakt.html">Kontakt</a>
      <a class="nav__link is-active" href="./gaestebuch.php">Gästebuch</a>
    </nav>

    <div class="sidebar__footer">
      <div class="sidebar__meta">
        <div class="meta__title">Kontakt</div>
        <div class="meta__line">Tel: <a href="tel:06767068618">0676 70 68 618</a></div>
        <div class="meta__line">E-Mail: <a href="mailto:office@räumungsmax.at">office@räumungsmax.at</a></div>
      </div>
    </div>
  </aside>

  <!-- Content -->
  <main class="content">

    <!-- Hero -->
    <section class="page-hero">
      <div class="page-hero__copy">
        <div class="kicker">Gästebuch</div>
        <h1>Kundenbewertungen</h1>
        <p class="lead">Ihre Meinung ist uns wichtig. Teilen Sie Ihre Erfahrung mit räumungsmax.at.</p>
      </div>
    </section>

    <!-- Formular -->
    <section class="section">
      <h2>Bewertung abgeben</h2>

      <div class="cards cards--2">
        <article class="card gb-card">
          <form method="post" action="./php/guestbook_add.php" class="gb-form">

            <input type="hidden" name="form_started" value="<?php echo time(); ?>">

            <!-- Honeypot -->
            <div class="hp-field" aria-hidden="true">
              <label for="website">Website</label>
              <input type="text" id="website" name="website" autocomplete="off" tabindex="-1">
            </div>

            <div class="gb-field">
              <span class="gb-icon" aria-hidden="true">👤</span>
              <input type="text" name="name" id="name" placeholder="Ihr Name (z. B. Frau H.)">
            </div>

            <div class="gb-rating-wrap">
              <label class="gb-label">Ihre Bewertung</label>

              <div class="gb-rating" role="radiogroup" aria-label="Bewertung">
                <input type="radio" name="rating" id="star5" value="5" required>
                <label for="star5" title="5 Sterne">★</label>

                <input type="radio" name="rating" id="star4" value="4">
                <label for="star4" title="4 Sterne">★</label>

                <input type="radio" name="rating" id="star3" value="3">
                <label for="star3" title="3 Sterne">★</label>

                <input type="radio" name="rating" id="star2" value="2">
                <label for="star2" title="2 Sterne">★</label>

                <input type="radio" name="rating" id="star1" value="1">
                <label for="star1" title="1 Stern">★</label>
              </div>
            </div>

            <div class="gb-field gb-field--textarea">
              <span class="gb-icon" aria-hidden="true">📝</span>
              <textarea name="message" id="message" rows="5" placeholder="Ihre Erfahrung (min. 10 Zeichen)" required></textarea>
            </div>

            <button type="submit" class="btn btn--primary gb-submit">
              Bewertung senden <span class="gb-arrow" aria-hidden="true">➜</span>
            </button>

          </form>
        </article>

        <aside class="card">
          <h3>Hinweis</h3>
          <p>Alle Bewertungen werden vor der Veröffentlichung geprüft. Vielen Dank für Ihr Feedback!</p>
        </aside>
      </div>
    </section>

    <!-- Bewertungen -->
    <section class="section">
      <h2>Letzte Bewertungen</h2>

      <?php if (count($reviews) === 0): ?>
        <div class="card">
          <p class="muted">Noch keine Bewertungen vorhanden.</p>
          <p class="muted">Seien Sie der Erste und teilen Sie Ihre Erfahrung!</p>
        </div>
      <?php else: ?>
        <div class="cards cards--2">
          <?php foreach ($reviews as $r): ?>
            <article class="card">
              <div class="gb-review__top">
                <strong><?= e($r['name'] ?? 'Anonym') ?: 'Anonym' ?></strong>

                <span class="gb-stars" aria-label="<?= (int)($r['rating'] ?? 0) ?> von 5">
                  <?php for ($i=1; $i<=5; $i++): ?>
                    <span class="<?= $i <= (int)($r['rating'] ?? 0) ? 'is-on' : '' ?>">★</span>
                  <?php endfor; ?>
                </span>
              </div>

              <p class="muted gb-review__date"><?= e($r['createdAt'] ?? '') ?></p>
              <p><?= nl2br(e($r['message'] ?? '')) ?></p>

              <?php if (isAdmin()): ?>
                <form method="post" action="./php/guestbook_delete.php" style="margin-top:12px;">
                  <input type="hidden" name="id" value="<?= e($r['id'] ?? '') ?>">
                  <input type="hidden" name="csrf" value="<?= e(csrfToken()); ?>">
                  <button class="btn btn--ghost" type="submit" onclick="return confirm('Bewertung wirklich löschen?');">
                    Löschen
                  </button>
                </form>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <footer class="footer">
      <div>© <span id="year"></span> räumungsmax.at</div>

      <div class="footer__links">
        <a href="./impressum.html">Impressum</a>
        <a href="./datenschutz.html">Datenschutz</a>

        <?php if (isAdmin()): ?>
          <span class="footer__sep">·</span>
          <a href="./admin/logout.php" class="footer__admin">Logout</a>
        <?php else: ?>
          <span class="footer__sep">·</span>
          <a href="./admin/login.php" class="footer__admin">Admin</a>
        <?php endif; ?>
      </div>
    </footer>

  </main>
</div>

<!-- Mobile drawer -->
<div class="drawer-backdrop" id="drawerBackdrop" hidden></div>
<aside class="drawer" id="mobileDrawer" hidden>
  <div class="drawer__header">
    <div class="drawer__brand">
      <img src="./images/logo.png" alt="Logo">
      <span>Menü</span>
    </div>
    <button class="icon-btn" id="drawerCloseBtn">✕</button>
  </div>

  <nav class="drawer__nav">
    <a class="nav__link" href="./index.html">Home</a>
    <a class="nav__link" href="./entruempelung.html">Entrümpelung</a>
    <a class="nav__link" href="./raeumungen.html">Räumungen</a>
    <a class="nav__link" href="./uebersiedlungen.html">Übersiedlungen</a>
    <a class="nav__link" href="./verlassenschaft.html">Verlassenschaft</a>
    <a class="nav__link" href="./wohnungsraeumungen.html">Wohnungsräumungen</a>
    <a class="nav__link" href="./ankauf.html">Ankauf</a>
    <a class="nav__link" href="./kontakt.html">Kontakt</a>
    <a class="nav__link is-active" href="./gaestebuch.php">Gästebuch</a>
  </nav>
</aside>

<script src="./js/app.js"></script>
</body>
</html>