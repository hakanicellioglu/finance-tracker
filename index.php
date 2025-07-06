<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Finance Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center vh-100">
<div class="text-center">
    <h1>Finance Tracker</h1>
    <p>Lütfen giriş yapın veya kayıt olun.</p>
    <a href="login.php" class="btn btn-primary">Giriş Yap</a>
    <a href="register.php" class="btn btn-secondary ms-2">Kayıt Ol</a>
</div>
</body>
</html>
