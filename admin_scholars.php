<?php
include 'db_connect.php';

$message = '';

// =============================
// SCHOLAR: UPDATE (purok/activity/status)
// =============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_scholar'])) {
    $scholar_id        = (int)($_POST['scholar_id'] ?? 0);
    $purok             = mysqli_real_escape_string($conn, $_POST['purok'] ?? '');
    $activity          = mysqli_real_escape_string($conn, $_POST['activity_participation'] ?? '');
    $status            = mysqli_real_escape_string($conn, $_POST['status'] ?? '');
    $scholar_standing  = mysqli_real_escape_string($conn, $_POST['scholar_standing'] ?? '');

    $update_scholar = "UPDATE scholars SET 
        purok='$purok', 
        activity_participation='$activity',
        status='$status',
        scholar_standing='$scholar_standing'
        WHERE scholar_id=$scholar_id";

    if (mysqli_query($conn, $update_scholar)) {
        $message .= "<div class='alert alert-success'>✅ Scholar data updated successfully.</div>";
    } else {
        $message .= "<div class='alert alert-danger'>❌ Error: " . htmlspecialchars(mysqli_error($conn)) . "</div>";
    }
}

// =============================
// SCHOLAR: DELETE ATTENDANCE
// =============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_attendance'])) {
    $scholar_id = (int)($_POST['scholar_id'] ?? 0);
    if ($scholar_id > 0) {
        $del = "UPDATE scholars SET attendance_image=NULL, attendance_date=NULL WHERE scholar_id=$scholar_id";
        if (mysqli_query($conn, $del)) {
            $message .= "<div class='alert alert-success'>🗑️ Attendance record deleted.</div>";
        } else {
            $message .= "<div class='alert alert-danger'>❌ Error deleting attendance: " . htmlspecialchars(mysqli_error($conn)) . "</div>";
        }
    }
}

// =============================
// SCHOLAR: DELETE SCHOLAR
// =============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_scholar'])) {
    $scholar_id = (int)($_POST['scholar_id'] ?? 0);
    if ($scholar_id > 0) {
        $del_scholar = "DELETE FROM scholars WHERE scholar_id=$scholar_id";
        if (mysqli_query($conn, $del_scholar)) {
            $message .= "<div class='alert alert-success'>🗑️ Scholar deleted successfully.</div>";
        } else {
            $message .= "<div class='alert alert-danger'>❌ Error deleting scholar: " . htmlspecialchars(mysqli_error($conn)) . "</div>";
        }
    }
}

// =============================
// FETCH SCHOLARS WITH SEARCH & FILTER
// =============================
$scholar_search   = $_GET['scholar_search'] ?? '';
$scholar_status   = $_GET['scholar_status'] ?? '';
$scholar_standing = $_GET['scholar_standing'] ?? '';

$query_scholars = "SELECT * FROM scholars WHERE 1";

if ($scholar_search != '') {
    $query_scholars .= " AND full_name LIKE '%" . mysqli_real_escape_string($conn, $scholar_search) . "%'";
}
if ($scholar_status != '') {
    $query_scholars .= " AND status = '" . mysqli_real_escape_string($conn, $scholar_status) . "'";
}
if ($scholar_standing != '') {
    $query_scholars .= " AND scholar_standing = '" . mysqli_real_escape_string($conn, $scholar_standing) . "'";
}

$query_scholars .= " ORDER BY scholar_id DESC";
$scholars = mysqli_query($conn, $query_scholars);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Scholars | Smart Scholar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        background-color: #f4f7fa;
    }
    h2 {
        font-weight: 700;
        color: #333;
    }
    .card {
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
    .table th {
        background: linear-gradient(90deg, #007bff, #00c6ff);
        color: white;
        text-transform: uppercase;
    }
    .table td {
        vertical-align: middle;
    }
    .btn-sm {
        border-radius: 20px;
        padding: 4px 12px;
    }
    .filter-bar {
        background: white;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
</style>
</head>
<body>
<div class="container mt-4">
  <h2 class="mb-4 text-center">📊 Scholar Participation Monitoring</h2>

  <?php if ($message) echo $message; ?>
  <!-- Back to Dashboard Button -->
  <div class="mb-3">
    <a href="admin_dashboard.php" class="btn btn-secondary">
      ⬅️ Back to Dashboard
    </a>
  </div>

  <!-- Search & Filter -->
  <form method="GET" class="row mb-3 filter-bar">
    <div class="col-md-4">
      <input type="text" name="scholar_search" value="<?= htmlspecialchars($_GET['scholar_search'] ?? '') ?>" class="form-control" placeholder="🔍 Search by Name">
    </div>
    <div class="col-md-3">
      <select name="scholar_status" class="form-select">
        <option value="">All Status</option>
        <option value="Active" <?= ($_GET['scholar_status'] ?? '') === 'Active' ? 'selected' : '' ?>>Active</option>
        <option value="Inactive" <?= ($_GET['scholar_status'] ?? '') === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
      </select>
    </div>
    <div class="col-md-3">
      <select name="scholar_standing" class="form-select">
        <option value="">All Standing</option>
        <option value="Safe" <?= ($_GET['scholar_standing'] ?? '') === 'Safe' ? 'selected' : '' ?>>Safe</option>
        <option value="At Risk" <?= ($_GET['scholar_standing'] ?? '') === 'At Risk' ? 'selected' : '' ?>>At Risk</option>
      </select>
    </div>
    <div class="col-md-2 d-flex gap-2">
      <button type="submit" class="btn btn-primary w-100">Filter</button>
      <a href="admin_scholars.php" class="btn btn-secondary w-100">Reset</a>
    </div>
  </form>
  <div class="d-flex justify-content-end mb-3">
  <a href="print_scholars.php" target="_blank" class="btn btn-secondary">🖨️ Print Scholars</a>
</div>


  <!-- Scholars Table -->
  <div class="card">
    <div class="card-body table-responsive">
      <table class="table table-bordered table-hover align-middle text-center">
        <thead>
          <tr>
            <th>Name</th>
            <th>Age</th>
            <th>Purok</th>
            <th>Activity</th>
            <th>Status</th>
            <th>Standing</th>
            <th>Actions</th>
            <th>Attendance</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($scholars && mysqli_num_rows($scholars) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($scholars)) { ?>
              <tr>
                <form method="POST">
                  <td><?= htmlspecialchars($row['full_name']) ?></td>
                  <td><?= htmlspecialchars($row['age'] ?? 'N/A') ?></td>
                  <td><input type="text" name="purok" value="<?= htmlspecialchars($row['purok'] ?? '') ?>" class="form-control" /></td>
                  <td><input type="text" name="activity_participation" value="<?= htmlspecialchars($row['activity_participation'] ?? '') ?>" class="form-control" /></td>
                  <td>
                    <select name="status" class="form-select">
                      <option value="Active" <?= ($row['status'] ?? '') === 'Active' ? 'selected' : '' ?>>Active</option>
                      <option value="Inactive" <?= ($row['status'] ?? '') === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                  </td>
                  <td>
                    <select name="scholar_standing" class="form-select">
                      <option value="Safe" <?= ($row['scholar_standing'] ?? '') === 'Safe' ? 'selected' : '' ?>>Safe</option>
                      <option value="At Risk" <?= ($row['scholar_standing'] ?? '') === 'At Risk' ? 'selected' : '' ?>>At Risk</option>
                    </select>
                  </td>
                  <td>
                    <input type="hidden" name="scholar_id" value="<?= (int)$row['scholar_id'] ?>">
                    <button type="submit" name="update_scholar" class="btn btn-success btn-sm mb-1">💾 Save</button>
                    <button type="submit" name="delete_scholar" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this scholar?')">🗑️ Delete</button>
                  </td>
                  <td>
                    <?php if (!empty($row['attendance_image'])): ?>
                      <a href="<?= htmlspecialchars($row['attendance_image']) ?>" target="_blank">
                        <img src="<?= htmlspecialchars($row['attendance_image']) ?>" width="100" class="img-thumbnail">
                      </a>
                      <p><small><?= htmlspecialchars($row['attendance_date']) ?></small></p>
                      <button type="submit" name="delete_attendance" class="btn btn-sm btn-danger mt-1" onclick="return confirm('Delete this attendance record?')">Delete</button>
                      <input type="hidden" name="scholar_id" value="<?= (int)$row['scholar_id'] ?>">
                    <?php else: ?>
                      <span class="text-muted">No record</span>
                    <?php endif; ?>
                  </td>
                </form>
              </tr>
            <?php } ?>
          <?php else: ?>
            <tr><td colspan="8" class="text-center text-muted">No scholars found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
</body>
</html>
