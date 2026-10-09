<?php // الشاشة الرئيسية: سؤال اليوم + النقاط + الإجابة
require 'config.php'; require 'layout.php';
require_login();
$q = today_q();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $q) {
    check_csrf();
    $m = me(); $v = norm($_POST['ans'] ?? '');
    if (!empty($m['done'][today()])) flash('ok', 'أجبت عن سؤال اليوم. عُد غدًا لسؤال جديد.');
    elseif ($v === '') flash('err', 'اكتب إجابتك أولًا.');
    elseif (answer_ok($v, $q[1])) {
        update_me(function (&$m) { $m['score'] += 10; $m['done'][today()] = 1; });
        flash('ok', 'أحسنت! إجابة صحيحة، +١٠ نقاط.', 'celebrate');
    } else flash('err', 'إجابة غير صحيحة، حاول مرة أخرى.', 'shake');
    header('Location: home.php'); exit;
}
$m = me(); $done = !empty($m['done'][today()]); $f = flash_take();
if (!$f && $done) $f = ['type' => 'ok', 'text' => 'أجبت عن سؤال اليوم. عُد غدًا لسؤال جديد.', 'fx' => ''];
page_start('رمضاني', true); ?>
  <div class="card score"><span class="tag">أهلًا <?= h($m['name']) ?></span><div>نقاطك</div><div class="n"><?= ar($m['score']) ?></div></div>
  <div class="card">
    <span class="tag">سؤال اليوم</span>
    <div class="q"><?= $q ? h($q[0]) : 'لا توجد أسئلة بعد. أضف أسئلة في ملف data/questions.csv' ?></div>
  </div>
  <form class="card" method="post">
    <?= csrf_field() ?>
    <label for="ans">إجابتك</label>
    <input id="ans" name="ans" autocomplete="off" placeholder="اكتب الإجابة هنا" <?= ($done || !$q) ? 'disabled' : '' ?>>
    <button class="btn" <?= ($done || !$q) ? 'disabled' : '' ?>>إرسال الإجابة</button>
    <div class="msg <?= h($f['type'] ?? '') ?>" role="alert"><?= h($f['text'] ?? '') ?></div>
  </form>
<?php page_end(true, 'home.php', $f['fx'] ?? '');
