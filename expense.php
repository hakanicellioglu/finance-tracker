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

// Handle add, edit actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $date = $_POST['date'] ?? '';
    $category = $_POST['category'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    $method = $_POST['method'] ?? '';
    $bank = $_POST['bank'] ?? '';
    if ($action === 'add') {
        $stmt = $conn->prepare('INSERT INTO expenses (user_id, date, category, amount, method, bank) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('issdss', $userId, $date, $category, $amount, $method, $bank);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'edit') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $conn->prepare('UPDATE expenses SET date=?, category=?, amount=?, method=?, bank=? WHERE id=? AND user_id=?');
        $stmt->bind_param('ssdsiii', $date, $category, $amount, $method, $bank, $id, $userId);
        $stmt->execute();
        $stmt->close();
    }
}

if (isset($_GET['delete'])) {
    $delId = intval($_GET['delete']);
    $stmt = $conn->prepare('DELETE FROM expenses WHERE id=? AND user_id=?');
    $stmt->bind_param('ii', $delId, $userId);
    $stmt->execute();
    $stmt->close();
    header('Location: expense.php');
    exit;
}

$stmt = $conn->prepare('SELECT id, date, category, amount, method, bank FROM expenses WHERE user_id=? ORDER BY date DESC');
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$expenses = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$categories = ['Gıda', 'Ulaşım', 'Fatura'];
$banks = ['Banka A', 'Banka B', 'Banka C'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Giderler</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<?php include 'header.php'; ?>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Giderler</h2>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">Gider Ekle</button>
    </div>
    <table id="expense-table" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Tarih</th>
                <th>Kategori</th>
                <th>Tutar</th>
                <th>İşlem Yeri</th>
                <th>Banka</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($expenses as $expense): ?>
            <tr>
                <td><?php echo htmlspecialchars($expense['date']); ?></td>
                <td><?php echo htmlspecialchars($expense['category']); ?></td>
                <td><?php echo number_format($expense['amount'], 2, ',', '.'); ?></td>
                <td><?php echo htmlspecialchars($expense['method']); ?></td>
                <td><?php echo htmlspecialchars($expense['bank']); ?></td>
                <td>
                    <button class="btn btn-sm btn-primary edit-btn" data-bs-toggle="modal" data-bs-target="#editModal"
                        data-id="<?php echo $expense['id']; ?>" data-date="<?php echo $expense['date']; ?>"
                        data-category="<?php echo htmlspecialchars($expense['category'], ENT_QUOTES); ?>"
                        data-amount="<?php echo $expense['amount']; ?>" data-method="<?php echo htmlspecialchars($expense['method'], ENT_QUOTES); ?>"
                        data-bank="<?php echo htmlspecialchars($expense['bank'], ENT_QUOTES); ?>">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <a href="?delete=<?php echo $expense['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu gideri silmek istediğinize emin misiniz?');">
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
        <h5 class="modal-title">Gider Ekle</h5>
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
            <label class="form-label">Tutar</label>
            <input type="number" step="0.01" class="form-control" name="amount" required>
          </div>
          <div class="mb-3">
            <label class="form-label">İşlem Yeri</label>
            <select class="form-select" name="method" id="add-method">
              <option value="Nakit">Nakit</option>
              <option value="Banka">Banka</option>
            </select>
          </div>
          <div class="mb-3" id="add-bank-group" style="display:none;">
            <label class="form-label">Banka</label>
            <select class="form-select" name="bank">
              <?php foreach ($banks as $bank): ?>
                <option value="<?php echo $bank; ?>"><?php echo $bank; ?></option>
              <?php endforeach; ?>
            </select>
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
        <h5 class="modal-title">Gideri Düzenle</h5>
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
            <label class="form-label">Tutar</label>
            <input type="number" step="0.01" class="form-control" name="amount" id="edit-amount" required>
          </div>
          <div class="mb-3">
            <label class="form-label">İşlem Yeri</label>
            <select class="form-select" name="method" id="edit-method">
              <option value="Nakit">Nakit</option>
              <option value="Banka">Banka</option>
            </select>
          </div>
          <div class="mb-3" id="edit-bank-group" style="display:none;">
            <label class="form-label">Banka</label>
            <select class="form-select" name="bank" id="edit-bank">
              <?php foreach ($banks as $bank): ?>
                <option value="<?php echo $bank; ?>"><?php echo $bank; ?></option>
              <?php endforeach; ?>
            </select>
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
    $('#expense-table').DataTable();

    function toggleBank(select, group) {
        if (select.val() === 'Banka') {
            group.show();
        } else {
            group.hide();
        }
    }

    toggleBank($('#add-method'), $('#add-bank-group'));
    $('#add-method').on('change', function(){
        toggleBank($(this), $('#add-bank-group'));
    });

    $('#editModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        $('#edit-id').val(button.data('id'));
        $('#edit-date').val(button.data('date'));
        $('#edit-category').val(button.data('category'));
        $('#edit-amount').val(button.data('amount'));
        $('#edit-method').val(button.data('method'));
        $('#edit-bank').val(button.data('bank'));
        toggleBank($('#edit-method'), $('#edit-bank-group'));
    });

    $('#edit-method').on('change', function(){
        toggleBank($(this), $('#edit-bank-group'));
    });
});
</script>
</body>
</html>
