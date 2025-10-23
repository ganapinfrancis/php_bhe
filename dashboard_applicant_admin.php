<?php
include 'db_connect.php';

// =============================
// HANDLE FORM SUBMISSIONS
// =============================
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $applicant_id = (int)($_POST['applicant_id'] ?? 0);

    // Update applicant
    if (isset($_POST['update_applicant'])) {
        $interview_result = mysqli_real_escape_string($conn, $_POST['interview_result'] ?? '');
        $exam_result = mysqli_real_escape_string($conn, $_POST['exam_result'] ?? '');
        $exam_date = mysqli_real_escape_string($conn, $_POST['exam_date'] ?? '');
        $interview_date = mysqli_real_escape_string($conn, $_POST['interview_date'] ?? '');
        $scholar_status = mysqli_real_escape_string($conn, $_POST['scholar_status'] ?? 'Pending');
        $number_of_dependents = (int)($_POST['number_of_dependents'] ?? 0);
        $parent_employment_status = mysqli_real_escape_string($conn, $_POST['parent_employment_status'] ?? '');
        $gwa = mysqli_real_escape_string($conn, $_POST['gwa'] ?? '');

        mysqli_query($conn, "UPDATE applicants SET
            interview_result='$interview_result',
            exam_result='$exam_result',
            exam_date='$exam_date',
            interview_date='$interview_date',
            scholar_status='$scholar_status',
            number_of_dependents=$number_of_dependents,
            parent_employment_status='$parent_employment_status',
            gwa='$gwa'
            WHERE applicant_id=$applicant_id");
    }

    // Upload exam result file
    if (isset($_POST['upload_exam_result']) && $applicant_id > 0) {
        if (isset($_FILES['exam_result_file']) && $_FILES['exam_result_file']['error'] === UPLOAD_ERR_OK) {
            $targetDir = "uploads/exam_results/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $fileTmp  = $_FILES['exam_result_file']['tmp_name'];
            $fileName = basename($_FILES['exam_result_file']['name']);
            $fileExt  = pathinfo($fileName, PATHINFO_EXTENSION);
            $newName  = "exam_result_" . $applicant_id . "_" . time() . "." . $fileExt;
            $filePath = $targetDir . $newName;

            if (move_uploaded_file($fileTmp, $filePath)) {
                mysqli_query($conn, "UPDATE applicants SET exam_result_file='" . mysqli_real_escape_string($conn, $filePath) . "' WHERE applicant_id=$applicant_id");
            }
        }
    }

    // Delete exam result file
    if (isset($_POST['delete_exam_result']) && $applicant_id > 0) {
        $res = mysqli_query($conn, "SELECT exam_result_file FROM applicants WHERE applicant_id=$applicant_id");
        if ($row = mysqli_fetch_assoc($res)) {
            if (!empty($row['exam_result_file']) && file_exists($row['exam_result_file'])) {
                unlink($row['exam_result_file']); // delete actual file
            }
        }
        mysqli_query($conn, "UPDATE applicants SET exam_result_file=NULL WHERE applicant_id=$applicant_id");
    }

    // Delete applicant
    if (isset($_POST['delete_applicant'])) {
        mysqli_query($conn, "DELETE FROM applicants WHERE applicant_id=$applicant_id");
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// =============================
// FETCH ALL APPLICANTS
// =============================
$search = $_GET['search'] ?? '';
$filter_status = $_GET['status'] ?? '';

$query = "SELECT * FROM applicants WHERE 1";

if ($search != '') {
    $query .= " AND full_name LIKE '%" . mysqli_real_escape_string($conn, $search) . "%'";
}
if ($filter_status != '') {
    $query .= " AND scholar_status = '" . mysqli_real_escape_string($conn, $filter_status) . "'";
}

$query .= " ORDER BY applicant_id DESC";
$applicants = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Applicant Evaluation | Smart Scholar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body { background: #f4f6f9; }
  .card { border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
  .card-header { font-size: 1.3rem; font-weight: bold; border-radius: 15px 15px 0 0; }
  table th { background: #f8f9fa; text-transform: uppercase; font-size: 0.9rem; letter-spacing: 0.5px; }
  table td { vertical-align: middle; }
  .btn-sm { border-radius: 8px; padding: 4px 10px; }
  .badge { font-size: 0.8rem; padding: 6px 10px; }
</style>
</head>
<body>

<div class="container mt-4">
  <!-- Back to Dashboard Button -->
  <div class="mb-3">
    <a href="admin_dashboard.php" class="btn btn-secondary">
      ⬅️ Back to Dashboard
    </a>
  </div>

  <div class="card mb-4">
    <div class="card-header bg-primary text-white">📋 Applicant Evaluation</div>
    
    <!-- Search + Filter Form -->
    <form method="GET" class="d-flex p-3">
      <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
             class="form-control form-control-sm me-2" placeholder="Search name...">
      <select name="status" class="form-select form-select-sm me-2">
        <option value="">All Status</option>
        <option value="Pending" <?= $filter_status==='Pending'?'selected':'' ?>>Pending</option>
        <option value="Scholar" <?= $filter_status==='Scholar'?'selected':'' ?>>Scholar</option>
        <option value="Not Scholar" <?= $filter_status==='Not Scholar'?'selected':'' ?>>Not Scholar</option>
      </select>
      <button type="submit" class="btn btn-light btn-sm">🔍 Search</button>
    </form>
  </div>

  <!-- ✅ Print Button -->
  <div class="d-flex justify-content-end mb-3">
    <a href="print_applicants.php" target="_blank" class="btn btn-secondary">🖨️ Print Applicants</a>
  </div>

  <div class="card-body table-responsive">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-light text-center">
        <tr>
          <th>Name</th>
          <th>Age</th>
          <th>Purok</th>
          <th>GWA (File)</th>
          <th>GWA (Admin Input)</th>
          <th>Proof of Employment</th>
          <th>📑 Proof of Dependents</th>
          <th>Dependents</th>
          <th>Parent Employment</th>
          <th>Interview Result</th>
          <th>Exam Score</th>
          <th>Exam Date</th>
          <th>Interview Date</th>
          <th>Scholar Status</th>
          <th>Actions</th>
          <th>AI Evaluation</th>
          <th>Upload test result</th>
        </tr>
      </thead>
      <tbody>
      <?php while($row = mysqli_fetch_assoc($applicants)) { ?>
        <tr>
          <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="applicant_id" value="<?= (int)$row['applicant_id'] ?>">

            <td><?= htmlspecialchars($row['full_name']) ?></td>
            <td><?= htmlspecialchars($row['age']) ?></td>
            <td><?= htmlspecialchars($row['purok']) ?></td>

            <!-- GWA Upload -->
            <td class="text-center">
              <?php if (!empty($row['gwa_image'])): ?>
                <a href="<?= htmlspecialchars($row['gwa_image']) ?>" target="_blank" class="btn btn-link">📄 View</a>
              <?php else: ?>
                <span class="badge bg-secondary">None</span>
              <?php endif; ?>
            </td>

            <!-- Admin Input for GWA -->
            <td class="text-center">
              <input type="number" step="0.01" min="1" max="5" 
                     name="gwa" 
                     value="<?= htmlspecialchars($row['gwa'] ?? '') ?>" 
                     class="form-control form-control-sm" placeholder="Enter GWA" />
            </td>

            <!-- Proof of Employment -->
            <td class="text-center">
              <?php if (!empty($row['proof_of_employment'])): ?>
                <a href="<?= htmlspecialchars($row['proof_of_employment']) ?>" target="_blank" class="btn btn-link">📂 View Proof</a>
              <?php else: ?>
                <span class="badge bg-secondary">None</span>
              <?php endif; ?>
            </td>

            <!-- Proof of Dependents -->
            <td class="text-center">
              <?php if (!empty($row['proof_of_dependents'])): ?>
                <a href="<?= htmlspecialchars($row['proof_of_dependents']) ?>" target="_blank" class="btn btn-link">📂 View Dependents</a>
              <?php else: ?>
                <span class="badge bg-secondary">None</span>
              <?php endif; ?>
            </td>

            <!-- Dependents -->
            <td>
              <input type="number" name="number_of_dependents" 
                     value="<?= htmlspecialchars($row['number_of_dependents'] ?? 0) ?>" 
                     class="form-control" />
            </td>

            <!-- Parent Employment -->
            <td>
              <select name="parent_employment_status" class="form-select">
                <option value="">-- Select --</option>
                <option value="Employed" <?= ($row['parent_employment_status'] ?? '') === 'Employed' ? 'selected' : '' ?>>Employed</option>
                <option value="Unemployed" <?= ($row['parent_employment_status'] ?? '') === 'Unemployed' ? 'selected' : '' ?>>Unemployed</option>
                <option value="Seasonal" <?= ($row['parent_employment_status'] ?? '') === 'Seasonal' ? 'selected' : '' ?>>Seasonal</option>
              </select>
            </td>

            <!-- Inputs -->
            <td><input type="text" name="interview_result" value="<?= htmlspecialchars($row['interview_result'] ?? '') ?>" class="form-control" /></td>
            <td><input type="text" name="exam_result" value="<?= htmlspecialchars($row['exam_result'] ?? '') ?>" class="form-control" /></td>
            <td><input type="date" name="exam_date" value="<?= htmlspecialchars($row['exam_date'] ?? '') ?>" class="form-control" /></td>
            <td><input type="date" name="interview_date" value="<?= htmlspecialchars($row['interview_date'] ?? '') ?>" class="form-control" /></td>

            <!-- Scholar Status -->
            <td>
              <select name="scholar_status" class="form-select">
                <option value="Pending" <?= ($row['scholar_status'] ?? '') === 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Scholar" <?= ($row['scholar_status'] ?? '') === 'Scholar' ? 'selected' : '' ?>>Scholar</option>
                <option value="Not Scholar" <?= ($row['scholar_status'] ?? '') === 'Not Scholar' ? 'selected' : '' ?>>Not Scholar</option>
              </select>
            </td>

            <!-- Save & Delete Applicant -->
            <td class="text-center">
              <button type="submit" name="update_applicant" class="btn btn-success btn-sm me-2">💾 Save</button>
              <button type="submit" name="delete_applicant" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this applicant?');">🗑️ Delete</button>
            </td>

            <!-- AI Evaluation -->
            <td class="text-center">
              <button type="button" class="btn btn-warning btn-sm" onclick="window.open('backend/checker.php?applicant_id=<?= $row['applicant_id'] ?>','_blank')">⚡ Run AI</button>
            </td>

            <!-- Upload Exam Result -->
            <td>
              <input type="file" name="exam_result_file" accept="application/pdf,image/*" class="form-control form-control-sm mb-1">
              <button type="submit" name="upload_exam_result" class="btn btn-sm btn-primary">⬆️ Upload</button>

              <?php if (!empty($row['exam_result_file'])): ?>
                <a href="<?= htmlspecialchars($row['exam_result_file']) ?>" target="_blank" class="d-block mt-1">📄 View</a>
                <button type="submit" name="delete_exam_result" class="btn btn-sm btn-danger mt-1">❌ Delete</button>
              <?php endif; ?>
            </td>

          </form>
        </tr>
      <?php } ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>
