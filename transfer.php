<?php
// ملف db.php
$host = 'localhost';
$db   = 'ewallet';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
try {
     $conn = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
     die("خطأ في الاتصال: " . $e->getMessage());
}
?>

<!-- ملف index.html -->
<!DOCTYPE html>
<html lang="ar">
<head>
  <meta charset="UTF-8">
  <title>محفظتي الرقمية</title>
</head>
<body>
  <h1>مرحبًا بك في محفظتك الرقمية</h1>
  <p><a href="signup.php">تسجيل</a> | <a href="login.php">دخول</a></p>
  <p><a href="admin.php">لوحة تحكم المدير</a></p>
</body>
</html>

<?php
// ملف signup.php
require_once 'db.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    if ($stmt->execute([$username, $password])) {
        echo "✅ تم التسجيل بنجاح.";
    } else {
        echo "❌ فشل في إنشاء الحساب.";
    }
}
?>
<form method="post">
    <label>اسم المستخدم:</label><input type="text" name="username" required><br>
    <label>كلمة المرور:</label><input type="password" name="password" required><br>
    <button type="submit">تسجيل</button>
</form>

<?php
// ملف login.php
require_once 'db.php';
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->execute([$_POST['username']]);
    $user = $stmt->fetch();
    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: wallet.php");
        exit();
    } else {
        echo "❌ بيانات خاطئة.";
    }
}
?>
<form method="post">
    <label>اسم المستخدم:</label><input type="text" name="username" required><br>
    <label>كلمة المرور:</label><input type="password" name="password" required><br>
    <button type="submit">دخول</button>
</form>

<?php
// ملف wallet.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
echo "<h1>أهلاً بك في محفظتك</h1>";
echo "<a href='transfer.php'>تحويل أموال</a> | <a href='logout.php'>تسجيل الخروج</a>";
?>

<?php
// ملف logout.php
session_start();
session_destroy();
header("Location: login.php");
?>

<?php
// ملف transfer.php
require_once 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender = $_SESSION['user_id'];
    $receiver = $_POST['receiver'];
    $amount = floatval($_POST['amount']);
    $fee = file_exists("fee.txt") ? floatval(file_get_contents("fee.txt")) : 0.001;
    $net = $amount - $fee;
    $stmt = $conn->prepare("INSERT INTO transactions (sender, receiver, amount) VALUES (?, ?, ?)");
    $stmt->execute([$sender, $receiver, $net]);
    echo "✅ تم التحويل مع خصم $fee BNB";
}
?>
<form method="post">
  <label>إلى (اسم المستخدم):</label><input type="text" name="receiver" required><br>
  <label>المبلغ:</label><input type="number" name="amount" step="0.0001" required><br>
  <button type="submit">تحويل</button>
</form>

<?php
// ملف admin.php
session_start();
require_once 'db.php';
if (!isset($_SESSION['admin_logged_in'])) {
    if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST['password'] === "admin123") {
        $_SESSION['admin_logged_in'] = true;
    } else {
        echo '<form method="post"><input type="password" name="password" placeholder="كلمة مرور المدير"><button type="submit">دخول</button></form>';
        exit();
    }
}
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['fee'])) {
    $fee = floatval($_POST['fee']);
    file_put_contents("fee.txt", $fee);
    echo "<p>✅ تم تحديث رسوم التحويل إلى $fee BNB</p>";
}
$fee = file_exists("fee.txt") ? file_get_contents("fee.txt") : "0.001";
?>
<h2>لوحة تحكم المدير</h2>
<form method="post">
    <label>رسوم التحويل (BNB):</label>
    <input type="text" name="fee" value="<?= htmlspecialchars($fee) ?>">
    <button type="submit">تحديث</button>
</form>
<p><a href="transactions.php">عرض كل التحويلات</a></p>

<?php
// ملف transactions.php
require_once 'db.php';
$stmt = $conn->query("SELECT * FROM transactions ORDER BY created_at DESC");
$rows = $stmt->fetchAll();
?>
<h2>سجل التحويلات</h2>
<table border="1">
<tr><th>من</th><th>إلى</th><th>المبلغ</th><th>الوقت</th></tr>
<?php foreach ($rows as $row): ?>
<tr>
    <td><?= htmlspecialchars($row['sender']) ?></td>
    <td><?= htmlspecialchars($row['receiver']) ?></td>
    <td><?= htmlspecialchars($row['amount']) ?></td>
    <td><?= $row['created_at'] ?></td>
</tr>
<?php endforeach; ?>
</table>
