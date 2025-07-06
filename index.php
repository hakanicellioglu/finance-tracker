<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<?php include 'header.php'; ?>
<div class="container mt-5 text-center">
    <h1>Finance Tracker</h1>
    <p>Lütfen giriş yapın veya kayıt olun.</p>
    <a href="login.php" class="btn btn-primary">Giriş Yap</a>
    <a href="register.php" class="btn btn-secondary ms-2">Kayıt Ol</a>
</div>
