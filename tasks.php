<?php // قائمة المهام اليومية: كل مهمة تمنح نقطة
require 'config.php'; require 'layout.php';
require_login();
$ids = array_column($TASKS, 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $id = $_POST['toggle'] ?? '';
    if (in_array($id, $ids, true)) {
        $had = !empty(me()['tasks'][today()][$id]);
        update_me(function (&$m) use ($id, $had) {
            if ($had) { unset($m['tasks'][today()][$id]); $m['score']--; }
            else { $m['tasks'][today()][$id] = 1; $m['score']++; }
        });
        if (!$had) flash('ok', '', count(me()['tasks'][today()]) === count($ids) ? 'celebrate' : 'plus');
    }
    header('Location: tasks.php'); exit;
}
$m = me(); $t = $m['tasks'][today()] ?? []; $n = count($t); $f = flash_take();
page_start('قائمة المهام', true); ?>
  <div class="card">
    <div style="font-weight:700">أنجزت <?= ar($n) ?> من <?= ar(count($ids)) ?> مهمة · نقاطك <?= ar($m['score']) ?></div>
    <div class="prog"><i style="width:<?= $n / count($ids) * 100 ?>%"></i></div>
    <?php foreach ($TASKS as [$id, $name]): $d = !empty($t[$id]); ?>
    <form method="post"><?= csrf_field() ?>
      <button class="task <?= $d ? 'd' : '' ?>" name="toggle" value="<?= h($id) ?>" aria-pressed="<?= $d ? 'true' : 'false' ?>"><span class="box">✓</span><span><?= h($name) ?></span></button>
    </form>
    <?php endforeach; ?>
  </div>
<?php page_end(true, 'tasks.php', $f['fx'] ?? '');
