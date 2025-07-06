<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username'] ?? '');
    $firstName  = trim($_POST['first_name'] ?? '');
    $lastName   = trim($_POST['last_name'] ?? '');
    $password   = $_POST['password'] ?? '';
    $confirm    = $_POST['confirm'] ?? '';

    if ($username !== '' && $firstName !== '' && $lastName !== '' && $password !== '' && $confirm !== '') {
        if ($password === $confirm) {
            $stmt = $conn->prepare('SELECT id FROM users WHERE username = ?');
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $error = 'Bu kullanıcı adı zaten alınmış.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $insert = $conn->prepare('INSERT INTO users (username, password, first_name, last_name) VALUES (?, ?, ?, ?)');
                $insert->bind_param('ssss', $username, $hash, $firstName, $lastName);
                if ($insert->execute()) {
                    $_SESSION['user_id'] = $insert->insert_id;
                    $_SESSION['user_name'] = $username;
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error = 'Kayıt işlemi sırasında bir hata oluştu.';
                }
                $insert->close();
            }
            $stmt->close();
        } else {
            $error = 'Şifreler uyuşmuyor.';
        }
    } else {
        $error = 'Lütfen tüm alanları doldurun.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kayıt Ol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5" style="max-width: 400px;">
    <h2 class="mb-4 text-center">Kayıt Ol</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post" action="">
        <div class="mb-3">
            <label for="username" class="form-label">Kullanıcı Adı</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="mb-3">
            <label for="first_name" class="form-label">İsim</label>
            <input type="text" class="form-control" id="first_name" name="first_name" required>
        </div>
        <div class="mb-3">
            <label for="last_name" class="form-label">Soyisim</label>
            <input type="text" class="form-control" id="last_name" name="last_name" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Şifre</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="mb-3">
            <label for="confirm" class="form-label">Şifre (Tekrar)</label>
            <input type="password" class="form-control" id="confirm" name="confirm" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Kayıt Ol</button>
    </form>
</div>
</body>
</html>
