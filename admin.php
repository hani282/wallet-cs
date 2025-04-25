<?php
session_start();
require_once 'db.php';

// التحقق من تسجيل دخول المسؤول
if (!isset($_SESSION['admin_logged_in'])) {
    if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST['password'] === "admin123") {
        $_SESSION['admin_logged_in'] = true;
    } else {
        echo '<form method="post"><input type="password" name="password" placeholder="كلمة مرور المدير"><button type="submit">دخول</button></form>';
        exit();
    }
}

// استلام التحديثات
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['fee'])) {
    $fee = floatval($_POST['fee']);
    file_put_contents("fee.txt", $fee);
    echo "<p>✅ تم تحديث رسوم التحويل إلى $fee BNB</p>";
}

// قراءة الرسوم الحالية
$fee = file_exists("fee.txt") ? file_get_contents("fee.txt") : "0.001";
?>

<h2>لوحة تحكم المدير</h2>
<form method="post">
    <label>رسوم التحويل (BNB):</label>
    <input type="text" name="fee" value="<?= htmlspecialchars($fee) ?>">
    <button type="submit">تحديث</button>
</form>

<p><a href="transactions.php">عرض كل التحويلات</a></p>
