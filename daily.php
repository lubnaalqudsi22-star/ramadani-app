<?php // صفحة رسالة اليوم
require 'config.php'; require 'layout.php';
require_login();
page_start('رسالة اليوم', true); ?>
  <div class="card">
    <span class="tag"><?= ar(date('Y/m/d')) ?></span>
    <p class="big"><?= h($MSGS[day_no() % count($MSGS)]) ?></p>
  </div>
<?php page_end(true, 'daily.php');
