<?php
// القالب المشترك: بداية الصفحة ونهايتها (مع الشريط العلوي والقائمة الجانبية)
function page_start($title, $app = false) { ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= h($title) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&family=Reem+Kufi:wght@600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div id="app"><section class="screen on">
<?php if ($app): ?>
<div class="top"><button class="ic" aria-label="القائمة الجانبية" onclick="drawer(true)">☰</button><span class="t"><?= h($title) ?></span></div>
<?php endif;
}

function page_end($app = false, $active = '', $fx = '') { ?>
</section>
<?php if ($app):
    $links = ['home.php'=>'الرئيسية','tasks.php'=>'١. قائمة المهام','daily.php'=>'٢. رسالة اليوم','adhkar.php'=>'٣. أذكار الصباح والمساء','logout.php'=>'تسجيل الخروج']; ?>
<div class="drawer-bg" onclick="drawer(false)"></div>
<aside class="drawer" aria-label="القائمة الجانبية">
  <div class="logo" style="font-size:36px;margin-bottom:14px">رمضاني</div>
  <nav class="nav">
  <?php foreach ($links as $u => $t): ?><a href="<?= $u ?>" class="<?= $u === $active ? 'on' : '' ?>"><?= $t ?></a><?php endforeach; ?>
  </nav>
</aside>
<?php endif; ?>
<div class="fx" id="fx"></div>
</div>
<script src="assets/app.js"></script>
<?php if ($fx): ?><script>runFx(<?= json_encode($fx) ?>)</script><?php endif; ?>
</body>
</html>
<?php }
