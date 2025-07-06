<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user = $_SESSION['user_name'] ?? 'Kullanıcı Adı';
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Finance Tracker</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link active" href="#">Anasayfa</a></li>
        <li class="nav-item"><a class="nav-link" href="income.php">Gelirler</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Giderler</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Yatırımlar</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Raporlar</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Ayarlar</a></li>
      </ul>
      <div class="d-flex align-items-center">
        <button class="btn btn-outline-secondary me-2" id="theme-toggle" title="Temayı Değiştir">
          <i class="bi bi-moon"></i>
        </button>
        <div class="dropdown">
          <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <?php echo htmlspecialchars($user); ?>
          </button>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li><a class="dropdown-item" href="logout.php">Çıkış Yap</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</nav>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
