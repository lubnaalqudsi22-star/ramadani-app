<?php
// الإعدادات والدوال المشتركة
date_default_timezone_set('Asia/Riyadh'); // غيّر المنطقة الزمنية حسب بلدك
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();
require __DIR__ . '/content.php';
const DATA_DIR = __DIR__ . '/data/';

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function ar($n) { return strtr((string)$n, ['0'=>'٠','1'=>'١','2'=>'٢','3'=>'٣','4'=>'٤','5'=>'٥','6'=>'٦','7'=>'٧','8'=>'٨','9'=>'٩']); }
function today() { return date('Y-m-d'); }
function day_no() { return (int)date('z') + 1; }

// ---- المستخدمون (ملف data/users.json) ----
function users() { $f = DATA_DIR . 'users.json'; return is_file($f) ? (json_decode(file_get_contents($f), true) ?: []) : []; }
function save_users($u) { file_put_contents(DATA_DIR . 'users.json', json_encode($u, JSON_UNESCAPED_UNICODE), LOCK_EX); }
function me() { $u = users(); $e = $_SESSION['email'] ?? ''; return $u[$e] ?? null; }
function update_me(callable $fn) { $u = users(); $e = $_SESSION['email']; $fn($u[$e]); save_users($u); }
function require_login() { if (!me()) { header('Location: login.php'); exit; } }

// ---- الأمان ----
function csrf() { if (empty($_SESSION['t'])) $_SESSION['t'] = bin2hex(random_bytes(16)); return $_SESSION['t']; }
function csrf_field() { return '<input type="hidden" name="t" value="' . h(csrf()) . '">'; }
function check_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals(csrf(), (string)($_POST['t'] ?? ''))) { http_response_code(400); exit('طلب غير صالح'); }
}

// ---- رسائل مؤقتة بين الصفحات ----
function flash($type, $text, $fx = '') { $_SESSION['flash'] = compact('type', 'text', 'fx'); }
function flash_take() { $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }

// ---- الأسئلة (ملف data/questions.csv) ----
function norm($s) {
    $s = preg_replace('/[\x{064B}-\x{065F}\x{0640}]/u', '', (string)$s);
    $s = strtr($s, ['أ'=>'ا','إ'=>'ا','آ'=>'ا','ة'=>'ه','ى'=>'ي','٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9']);
    $s = preg_replace('/\s+/u', ' ', trim($s));
    $s = preg_replace('/^ال/u', '', $s);
    return mb_strtolower($s, 'UTF-8');
}
function questions() {
    $out = [];
    $h = @fopen(DATA_DIR . 'questions.csv', 'r');
    if (!$h) return $out;
    $line = fgets($h); rewind($h);
    $d = substr_count($line, ';') > substr_count($line, ',') ? ';' : ','; // Excel قد يستخدم ;
    $first = true;
    while (($r = fgetcsv($h, 0, $d)) !== false) {
        if ($first) { $first = false; continue; } // سطر العناوين
        $q = trim($r[0] ?? '');
        $a = array_values(array_filter(array_map('trim', explode('|', $r[1] ?? '')), 'strlen'));
        if ($q !== '' && $a) $out[] = [$q, $a];
    }
    fclose($h);
    return $out;
}
function today_q() { $q = questions(); return $q ? $q[day_no() % count($q)] : null; }
function answer_ok($v, $answers) {
    foreach ($answers as $a) {
        $n = norm($a);
        if ($v === $n || (mb_strlen($n) > 2 && mb_strpos($v, $n) !== false)) return true;
    }
    return false;
}
