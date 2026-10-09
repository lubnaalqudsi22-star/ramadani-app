<?php // شاشة تسجيل الدخول / إنشاء حساب + التحقق برمز
require 'config.php'; require 'layout.php';
if (me()) { header('Location: home.php'); exit; }
if (isset($_GET['reset'])) { unset($_SESSION['pending'], $_SESSION['otp']); header('Location: login.php'); exit; }

$msg = ''; $up = ($_GET['m'] ?? '') === 'up';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    if (($_POST['a'] ?? '') === 'auth') {
        $up = ($_POST['mode'] ?? '') === 'up';
        $e = mb_strtolower(trim($_POST['email'] ?? '')); $p = (string)($_POST['pass'] ?? ''); $u = users();
        if (!filter_var($e, FILTER_VALIDATE_EMAIL)) $msg = 'اكتب بريدًا إلكترونيًا صحيحًا.';
        elseif (strlen($p) < 6) $msg = 'كلمة المرور يجب ألا تقل عن ٦ أحرف.';
        elseif ($up) {
            $n = trim($_POST['name'] ?? '');
            if ($n === '') $msg = 'اكتب اسمك.';
            elseif (isset($u[$e])) $msg = 'هذا البريد مسجّل من قبل. جرّب «دخول».';
            else { $u[$e] = ['name'=>$n, 'hash'=>password_hash($p, PASSWORD_DEFAULT), 'score'=>0, 'done'=>[], 'tasks'=>[], 'adhkar'=>[]]; save_users($u); }
        } elseif (!isset($u[$e]) || !password_verify($p, $u[$e]['hash'])) $msg = 'البريد أو كلمة المرور غير صحيحة.';
        if ($msg === '') { $_SESSION['pending'] = $e; $_SESSION['otp'] = (string)random_int(1000, 9999); $_SESSION['tries'] = 0; }
    } elseif (isset($_SESSION['pending'])) {
        if (hash_equals($_SESSION['otp'], norm($_POST['otp'] ?? ''))) {
            session_regenerate_id(true);
            $_SESSION['email'] = $_SESSION['pending']; unset($_SESSION['pending'], $_SESSION['otp']);
            header('Location: home.php'); exit;
        }
        $msg = 'الرمز غير صحيح. أعد المحاولة.';
        if (++$_SESSION['tries'] >= 5) { unset($_SESSION['pending'], $_SESSION['otp']); $msg = 'تجاوزت عدد المحاولات، ابدأ من جديد.'; }
    }
}
page_start('تسجيل الدخول'); ?>
  <div class="logo" style="font-size:40px">رمضاني</div>
<?php if (isset($_SESSION['pending'])): ?>
  <form method="post">
    <?= csrf_field() ?>
    <h2>التحقق من الهوية</h2>
    <div class="demo">نسخة تجريبية بلا خدمة رسائل. رمز التحقق الخاص بك:<br><b><?= ar($_SESSION['otp']) ?></b></div>
    <label for="otp">أدخل الرمز المكوّن من ٤ أرقام</label>
    <input id="otp" name="otp" inputmode="numeric" maxlength="4" dir="ltr" autocomplete="one-time-code" autofocus>
    <button class="btn">تأكيد</button>
  </form>
  <div class="msg err" role="alert"><?= h($msg) ?></div>
  <a class="btn alt" style="margin-top:auto" href="login.php?reset=1">إلغاء</a>
<?php else: ?>
  <div class="tabs"><a href="login.php" class="<?= $up ? '' : 'on' ?>">دخول</a><a href="login.php?m=up" class="<?= $up ? 'on' : '' ?>">حساب جديد</a></div>
  <form method="post">
    <?= csrf_field() ?><input type="hidden" name="a" value="auth"><input type="hidden" name="mode" value="<?= $up ? 'up' : 'in' ?>">
    <?php if ($up): ?><label for="name">الاسم</label><input id="name" name="name" autocomplete="name" value="<?= h($_POST['name'] ?? '') ?>"><?php endif; ?>
    <label for="email">البريد الإلكتروني</label>
    <input id="email" name="email" type="email" dir="ltr" autocomplete="email" placeholder="name@example.com" value="<?= h($_POST['email'] ?? '') ?>">
    <label for="pass">كلمة المرور (٦ أحرف على الأقل)</label>
    <input id="pass" name="pass" type="password" dir="ltr" autocomplete="<?= $up ? 'new-password' : 'current-password' ?>">
    <button class="btn"><?= $up ? 'إنشاء الحساب' : 'دخول' ?></button>
  </form>
  <div class="msg err" role="alert"><?= h($msg) ?></div>
  <a class="btn alt" style="margin-top:auto" href="index.php">رجوع</a>
<?php endif; ?>
<?php page_end();
