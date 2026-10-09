<?php // أذكار الصباح والمساء مع عدّاد لكل ذكر
require 'config.php'; require 'layout.php';
require_login();
$s = $_REQUEST['set'] ?? '';
$set = in_array($s, ['am', 'pm'], true) ? $s : (date('G') < 12 ? 'am' : 'pm');
$list = $ADHKAR[$set];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $i = (int)($_POST['i'] ?? -1);
    if (isset($list[$i])) {
        $m = me(); $st = $m['adhkar'][today()][$set] ?? [];
        if (($st[$i] ?? 0) < $list[$i][1]) {
            $st[$i] = ($st[$i] ?? 0) + 1;
            $all = true; foreach ($list as $j => $d) if (($st[$j] ?? 0) < $d[1]) $all = false;
            $new = $all && empty($m['tasks'][today()][$set]);
            update_me(function (&$m) use ($set, $st, $new) {
                $m['adhkar'][today()][$set] = $st;
                if ($new) { $m['tasks'][today()][$set] = 1; $m['score']++; }
            });
            if ($new) flash('ok', '', 'celebrate');
        }
    }
    header('Location: adhkar.php?set=' . $set); exit;
}
$st = me()['adhkar'][today()][$set] ?? []; $done = 0; $f = flash_take();
foreach ($list as $j => $d) if (($st[$j] ?? 0) >= $d[1]) $done++;
$name = $set === 'am' ? 'أذكار الصباح' : 'أذكار المساء';
page_start('الأذكار', true); ?>
  <div class="tabs"><a href="adhkar.php?set=am" class="<?= $set === 'am' ? 'on' : '' ?>">أذكار الصباح</a><a href="adhkar.php?set=pm" class="<?= $set === 'pm' ? 'on' : '' ?>">أذكار المساء</a></div>
  <div style="font-weight:700"><?= $done === count($list) ? "أتممت $name وسُجّلت في قائمة المهام ✓" : 'أنجزت ' . ar($done) . ' من ' . ar(count($list)) . ' ذكرًا' ?></div>
  <div class="prog"><i style="width:<?= $done / count($list) * 100 ?>%"></i></div>
  <?php foreach ($list as $i => [$txt, $c]): $n = $st[$i] ?? 0; $fin = $n >= $c; ?>
  <form class="card" method="post"><?= csrf_field() ?><input type="hidden" name="set" value="<?= $set ?>"><input type="hidden" name="i" value="<?= $i ?>">
    <div class="dh <?= $fin ? 'd' : '' ?>"><?= h($txt) ?></div>
    <button class="btn cnt" <?= $fin ? 'disabled' : '' ?>><?= $fin ? 'تم ✓' : 'اضغط للعد: ' . ar($n) . ' / ' . ar($c) ?></button>
  </form>
  <?php endforeach; ?>
<?php page_end(true, 'adhkar.php', $f['fx'] ?? '');
