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

// Handle add and edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action'] ?? '';
    $date    = $_POST['date'] ?? '';
    $category = $_POST['category'] ?? '';
    $code    = $_POST['code'] ?? '';
    $exchange = $_POST['exchange'] ?? '';
    $unit    = $_POST['unit'] ?? 0;

    if ($action === 'add') {
        $stmt = $conn->prepare('INSERT INTO investments (user_id, date, category, code, exchange, unit) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('issssi', $userId, $date, $category, $code, $exchange, $unit);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'edit') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $conn->prepare('UPDATE investments SET date=?, category=?, code=?, exchange=?, unit=? WHERE id=? AND user_id=?');
        $stmt->bind_param('sssssii', $date, $category, $code, $exchange, $unit, $id, $userId);
        $stmt->execute();
        $stmt->close();
    }
}

if (isset($_GET['delete'])) {
    $delId = intval($_GET['delete']);
    $stmt = $conn->prepare('DELETE FROM investments WHERE id=? AND user_id=?');
    $stmt->bind_param('ii', $delId, $userId);
    $stmt->execute();
    $stmt->close();
    header('Location: invest.php');
    exit;
}

$stmt = $conn->prepare('SELECT id, date, category, code, exchange, unit FROM investments WHERE user_id=? ORDER BY date DESC');
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$investments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$categories = ['Hisse', 'Döviz', 'Altın'];
$exchanges = ['BIST', 'NASDAQ', 'NYSE'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yatırımlar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<?php include 'header.php'; ?>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Yatırımlar</h2>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">Yatırım Ekle</button>
    </div>
    <table id="invest-table" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Tarih</th>
                <th>Kategori</th>
                <th>Kod</th>
                <th>Borsa</th>
                <th>Birim</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($investments as $inv): ?>
            <tr>
                <td><?php echo htmlspecialchars($inv['date']); ?></td>
                <td><?php echo htmlspecialchars($inv['category']); ?></td>
                <td><?php echo htmlspecialchars($inv['code']); ?></td>
                <td><?php echo htmlspecialchars($inv['exchange']); ?></td>
                <td><?php echo htmlspecialchars($inv['unit']); ?></td>
                <td>
                    <button class="btn btn-sm btn-primary edit-btn" data-bs-toggle="modal" data-bs-target="#editModal"
                        data-id="<?php echo $inv['id']; ?>" data-date="<?php echo $inv['date']; ?>"
                        data-category="<?php echo htmlspecialchars($inv['category'], ENT_QUOTES); ?>"
                        data-code="<?php echo htmlspecialchars($inv['code'], ENT_QUOTES); ?>"
                        data-exchange="<?php echo htmlspecialchars($inv['exchange'], ENT_QUOTES); ?>"
                        data-unit="<?php echo $inv['unit']; ?>">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <a href="?delete=<?php echo $inv['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu yatırımı silmek istediğinize emin misiniz?');">
                        <i class="bi bi-trash"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Yatırım Ekle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" action="">
        <input type="hidden" name="action" value="add">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Tarih</label>
            <input type="date" class="form-control" name="date" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Kategori</label>
            <select class="form-select" name="category">
              <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Kod</label>
            <input type="text" class="form-control" name="code" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Borsa</label>
            <select class="form-select" name="exchange">
              <?php foreach ($exchanges as $ex): ?>
                <option value="<?php echo $ex; ?>"><?php echo $ex; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Birim</label>
            <input type="number" step="0.01" class="form-control" name="unit" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
          <button type="submit" class="btn btn-primary">Kaydet</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Yatırımı Düzenle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" action="">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" id="edit-id">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Tarih</label>
            <input type="date" class="form-control" name="date" id="edit-date" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Kategori</label>
            <select class="form-select" name="category" id="edit-category">
              <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Kod</label>
            <input type="text" class="form-control" name="code" id="edit-code" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Borsa</label>
            <select class="form-select" name="exchange" id="edit-exchange">
              <?php foreach ($exchanges as $ex): ?>
                <option value="<?php echo $ex; ?>"><?php echo $ex; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Birim</label>
            <input type="number" step="0.01" class="form-control" name="unit" id="edit-unit" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
          <button type="submit" class="btn btn-primary">Kaydet</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
$(function() {
    $('#invest-table').DataTable();

    $('#editModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        $('#edit-id').val(button.data('id'));
        $('#edit-date').val(button.data('date'));
        $('#edit-category').val(button.data('category'));
        $('#edit-code').val(button.data('code'));
        $('#edit-exchange').val(button.data('exchange'));
        $('#edit-unit').val(button.data('unit'));
    });
});
</script>
</body>
</html>
