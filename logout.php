<?php // شاشة تسجيل الخروج مع التحقق بكلمة المرور
require 'config.php'; require 'layout.php';
$m = me(); $msg = '';
if ($m && $_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    if (password_verify((string)($_POST['pass'] ?? ''), $m['hash'])) {
        $_SESSION = []; session_destroy(); header('Location: index.php'); exit;
    }
    $msg = 'كلمة المرور غير صحيحة.';
}
page_start('تسجيل الخروج'); ?>
  <div class="logo" style="font-size:40px">رمضاني</div>
<?php if (!$m): ?>
  <div class="card"><h2>لا يوجد حساب مسجّل</h2><p>أنت غير مسجّل الدخول حاليًا.</p></div>
<?php else: ?>
  <form class="card" method="post">
    <?= csrf_field() ?>
    <h2>تأكيد تسجيل الخروج</h2>
    <p>للتأكد أنك صاحب الحساب، أدخل كلمة المرور.</p>
    <label for="pass">كلمة المرور</label>
    <input id="pass" name="pass" type="password" dir="ltr" autocomplete="current-password" autofocus>
    <button class="btn">تسجيل الخروج</button>
  </form>
<?php endif; ?>
  <div class="msg err" role="alert"><?= h($msg) ?></div>
  <a class="btn alt" style="margin-top:auto" href="<?= $m ? 'home.php' : 'index.php' ?>">رجوع</a>
<?php page_end();
