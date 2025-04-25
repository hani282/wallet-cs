<?php
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
