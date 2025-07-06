<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch monthly income totals
$stmt = $conn->prepare("SELECT DATE_FORMAT(date, '%Y-%m') AS month, SUM(amount) AS total FROM incomes WHERE user_id=? GROUP BY month ORDER BY month DESC");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$incomeData = [];
while ($row = $result->fetch_assoc()) {
    $incomeData[$row['month']] = $row['total'];
}
$stmt->close();

// Fetch monthly expense totals
$stmt = $conn->prepare("SELECT DATE_FORMAT(date, '%Y-%m') AS month, SUM(amount) AS total FROM expenses WHERE user_id=? GROUP BY month ORDER BY month DESC");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$expenseData = [];
while ($row = $result->fetch_assoc()) {
    $expenseData[$row['month']] = $row['total'];
}
$stmt->close();

$months = array_unique(array_merge(array_keys($incomeData), array_keys($expenseData)));
rsort($months);

$totalIncome = 0;
$totalExpense = 0;
$rows = [];
foreach ($months as $m) {
    $income = $incomeData[$m] ?? 0;
    $expense = $expenseData[$m] ?? 0;
    $totalIncome += $income;
    $totalExpense += $expense;
    $rows[] = [
        'month' => $m,
        'income' => $income,
        'expense' => $expense,
        'net' => $income - $expense,
    ];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Raporlar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>
<?php include 'header.php'; ?>
<div class="container mt-5">
    <h2>Raporlar</h2>
    <table id="report-table" class="table table-bordered table-striped mt-4">
        <thead>
            <tr>
                <th>Ay</th>
                <th>Gelir</th>
                <th>Gider</th>
                <th>Net</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['month']); ?></td>
                <td><?php echo number_format($row['income'], 2, ',', '.'); ?></td>
                <td><?php echo number_format($row['expense'], 2, ',', '.'); ?></td>
                <td><?php echo number_format($row['net'], 2, ',', '.'); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th>Toplam</th>
                <th><?php echo number_format($totalIncome, 2, ',', '.'); ?></th>
                <th><?php echo number_format($totalExpense, 2, ',', '.'); ?></th>
                <th><?php echo number_format($totalIncome - $totalExpense, 2, ',', '.'); ?></th>
            </tr>
        </tfoot>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
$(function() {
    $('#report-table').DataTable();
});
</script>
</body>
</html>
