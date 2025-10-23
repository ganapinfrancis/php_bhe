<?php
// Start session safely
if (session_status() == PHP_SESSION_NONE) session_start();
include 'db_connect.php';

// Redirect kung hindi naka-login
if (!isset($_SESSION['applicant_email'])) {
    header("Location: index.html");
    exit;
}

$email = $_SESSION['applicant_email'];

// Kunin applicant data
$result = mysqli_query($conn, "SELECT * FROM applicants WHERE email = '".mysqli_real_escape_string($conn, $email)."' LIMIT 1");
$data = $result ? mysqli_fetch_assoc($result) : null;

if (!$data) {
    session_destroy();
    header("Location: index.html?msg=" . urlencode("Session expired. Please login again."));
    exit;
}

$applicant_id = (int)$data['applicant_id'];
$message = '';

/* =====================================================
   HANDLE DELETE & UPLOAD FOR GWA
   ===================================================== */
if (isset($_GET['delete_gwa']) && !empty($data['gwa_image'])) {
    if (file_exists($data['gwa_image'])) unlink($data['gwa_image']);
    mysqli_query($conn, "UPDATE applicants SET gwa_image='', gwa_status='pending' WHERE applicant_id=$applicant_id");
    $data['gwa_image'] = '';
    $data['gwa_status'] = 'pending';
    $message = "🗑️ GWA image deleted successfully.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['gwa_image'])) {
    $target_dir = "uploads/gwa/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    if (!empty($data['gwa_image']) && file_exists($data['gwa_image'])) unlink($data['gwa_image']);

    $file_name = time() . "_" . basename($_FILES["gwa_image"]["name"]);
    $target_file = $target_dir . $file_name;

    if ($_FILES['gwa_image']['error'] === 0 && move_uploaded_file($_FILES["gwa_image"]["tmp_name"], $target_file)) {
        mysqli_query($conn, "UPDATE applicants SET gwa_image='".mysqli_real_escape_string($conn,$target_file)."', gwa_status='uploaded' WHERE applicant_id=$applicant_id");
        $data['gwa_image'] = $target_file;
        $data['gwa_status'] = 'uploaded';
        $message = "✅ GWA image uploaded successfully!";
    } else {
        $message = "❌ Error uploading GWA file. Error code: ".$_FILES['gwa_image']['error'];
    }
}

/* =====================================================
   HANDLE UPLOAD FOR PROOF OF EMPLOYMENT
   ===================================================== */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['proof_of_employment'])) {
    $target_dir = "uploads/proof/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    if (!empty($data['proof_of_employment']) && file_exists($data['proof_of_employment'])) {
        unlink($data['proof_of_employment']);
    }

    $file_name = time() . "_" . basename($_FILES["proof_of_employment"]["name"]);
    $target_file = $target_dir . $file_name;

    if ($_FILES['proof_of_employment']['error'] === 0 && move_uploaded_file($_FILES["proof_of_employment"]["tmp_name"], $target_file)) {
        mysqli_query($conn, "UPDATE applicants SET proof_of_employment='".mysqli_real_escape_string($conn,$target_file)."', proof_status='uploaded' WHERE applicant_id=$applicant_id");
        $data['proof_of_employment'] = $target_file;
        $data['proof_status'] = 'uploaded';
        $message = "✅ Proof of Employment uploaded successfully!";
    } else {
        $message = "❌ Error uploading Proof of Employment. Error code: ".$_FILES['proof_of_employment']['error'];
    }
}

/* =====================================================
   HANDLE UPLOAD FOR PROOF OF DEPENDENTS
   ===================================================== */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['proof_of_dependents'])) {
    $target_dir = "uploads/dependents/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    if (!empty($data['proof_of_dependents']) && file_exists($data['proof_of_dependents'])) {
        unlink($data['proof_of_dependents']);
    }

    $file_name = time() . "_" . basename($_FILES["proof_of_dependents"]["name"]);
    $target_file = $target_dir . $file_name;

    if ($_FILES['proof_of_dependents']['error'] === 0 && move_uploaded_file($_FILES["proof_of_dependents"]["tmp_name"], $target_file)) {
        mysqli_query($conn, "UPDATE applicants SET proof_of_dependents='".mysqli_real_escape_string($conn,$target_file)."', dependents_status='uploaded' WHERE applicant_id=$applicant_id");
        $data['proof_of_dependents'] = $target_file;
        $data['dependents_status'] = 'uploaded';
        $message = "✅ Proof of Dependents uploaded successfully!";
    } else {
        $message = "❌ Error uploading Proof of Dependents. Error code: ".$_FILES['proof_of_dependents']['error'];
    }
}

/* =====================================================
   NOTE: exam_result_file is expected to be set by admin page.
   Here we only display it to the applicant (view / preview / download).
   ===================================================== */
$exam_file = $data['exam_result_file'] ?? '';
$exam_file_exists = (!empty($exam_file) && file_exists($exam_file));

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Applicant Dashboard | Smart Scholar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #f4f6f9; }
.card { margin-top: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.nav-tabs .nav-link { cursor: pointer; }
.embed-preview { width: 100%; height: 600px; border: 1px solid #ddd; border-radius: 6px; }
/* 👇 Para hindi sobrang laki ng images */
.img-preview {
    max-width: 200px;  /* palitan mo kung gusto mas maliit/larger */
    height: auto;
    border: 2px solid #ddd;
    border-radius: 8px;
    display: block;
    margin-bottom: 10px;
}
</style>
</head>
<body>

<div class="container py-4">
<?php if ($message): ?>
<div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<!-- Tabs -->
<ul class="nav nav-tabs" id="dashboardTabs" role="tablist">
  <li class="nav-item"><button class="nav-link active" id="gwa-tab" data-bs-toggle="tab" data-bs-target="#gwa" type="button">📄 GWA Upload</button></li>
  <li class="nav-item"><button class="nav-link" id="proof-tab" data-bs-toggle="tab" data-bs-target="#proof" type="button">📂 Proof of Employment</button></li>
  <li class="nav-item"><button class="nav-link" id="dependents-tab" data-bs-toggle="tab" data-bs-target="#dependents" type="button">👨‍👩‍👧 Proof of Dependents</button></li>
  <li class="nav-item"><button class="nav-link" id="exam-tab" data-bs-toggle="tab" data-bs-target="#exam" type="button">📝 Exam Schedule</button></li>
  <li class="nav-item"><button class="nav-link" id="results-tab" data-bs-toggle="tab" data-bs-target="#results" type="button">📊 Results</button></li>
  <li class="nav-item"><button class="nav-link" id="exam-proof-tab" data-bs-toggle="tab" data-bs-target="#exam-proof" type="button">📝 Exam Proof</button></li>
</ul>

<div class="tab-content">
  <!-- GWA Upload -->
  <div class="tab-pane fade show active" id="gwa">
    <div class="card">
      <div class="card-header bg-primary text-white">Upload / Replace GWA Image</div>
      <div class="card-body">
        <?php if (!empty($data['gwa_image'])): ?>
          <p>Uploaded Image:</p>
          <img src="<?= htmlspecialchars($data['gwa_image']) ?>" class="img-preview">
          <a href="?delete_gwa=1" class="btn btn-danger mb-3" onclick="return confirm('Delete uploaded GWA?')">Delete Image</a>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
          <input type="file" name="gwa_image" class="form-control mb-2" required>
          <button type="submit" class="btn btn-<?= !empty($data['gwa_image'])?'warning':'primary' ?>"><?= !empty($data['gwa_image'])?'Replace':'Upload' ?></button>
        </form>
      </div>
    </div>
  </div>

  <!-- Proof of Employment -->
  <div class="tab-pane fade" id="proof">
    <div class="card">
      <div class="card-header bg-success text-white">Upload Proof of Employment</div>
      <div class="card-body">
        <?php if (!empty($data['proof_of_employment'])): ?>
          <a href="<?= htmlspecialchars($data['proof_of_employment']) ?>" target="_blank" class="btn btn-link">📂 View Uploaded Proof</a>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
          <input type="file" name="proof_of_employment" class="form-control mb-2" required>
          <small class="form-text text-muted">
            📌 Pwede mong ipasa ang alinman sa mga sumusunod:<br>
            • Proof of Indigency <br>
            • ITR (Income Tax Return) o BIR Form 2316
          </small>
          <br>
          <button type="submit" class="btn btn-<?= !empty($data['proof_of_employment'])?'warning':'primary' ?>"><?= !empty($data['proof_of_employment'])?'Replace':'Upload' ?></button>
        </form>
      </div>
    </div>
  </div>

  <!-- Proof of Dependents -->
  <div class="tab-pane fade" id="dependents">
    <div class="card">
      <div class="card-header bg-warning text-white">Upload Proof of Dependents</div>
      <div class="card-body">
        <?php if (!empty($data['proof_of_dependents'])): ?>
          <a href="<?= htmlspecialchars($data['proof_of_dependents']) ?>" target="_blank" class="btn btn-link">👨‍👩‍👧 View Uploaded Proof</a>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
          <input type="file" name="proof_of_dependents" class="form-control mb-2" required>
          <small class="form-text text-muted">
            📌 Pwede mong ipasa ang alinman sa mga sumusunod:<br>
            • Birth Certificate ng anak/dependent <br>
            • Barangay Certificate na nagpapatunay ng dependency
          </small>
          <br>
          <button type="submit" class="btn btn-<?= !empty($data['proof_of_dependents'])?'warning':'primary' ?>"><?= !empty($data['proof_of_dependents'])?'Replace':'Upload' ?></button>
        </form>
      </div>
    </div>
  </div>

  <!-- Exam Schedule -->
  <div class="tab-pane fade" id="exam">
    <div class="card">
      <div class="card-header bg-info text-white">Exam & Interview Schedule</div>
      <div class="card-body">
        <p><strong>Exam Date:</strong> <?= !empty($data['exam_date'])?htmlspecialchars($data['exam_date']):'To be scheduled' ?></p>
        <p><strong>Interview Date:</strong> <?= !empty($data['interview_date'])?htmlspecialchars($data['interview_date']):'To be scheduled' ?></p>
      </div>
    </div>
  </div>

  <!-- Results -->
  <div class="tab-pane fade" id="results">
    <div class="card">
      <div class="card-header bg-secondary text-white">Results</div>
      <div class="card-body">
        <p><strong>Exam Result:</strong> <?= !empty($data['exam_result'])?htmlspecialchars($data['exam_result']):'Pending' ?></p>
        <p><strong>Interview Result:</strong> <?= !empty($data['interview_result'])?htmlspecialchars($data['interview_result']):'Pending' ?></p>
        <p><strong>Scholar Status:</strong>
          <?php $ss = $data['scholar_status'] ?? 'Pending';
          $badge = ($ss==='Scholar')?'bg-success':(($ss==='Not Scholar')?'bg-danger':'bg-secondary'); ?>
          <span class="badge <?= $badge ?>"><?= htmlspecialchars($ss) ?></span>
        </p>
      </div>
    </div>
  </div>

  <!-- Exam Proof (NEW) -->
  <div class="tab-pane fade" id="exam-proof">
    <div class="card">
      <div class="card-header bg-primary text-white">Exam Proof</div>
      <div class="card-body">
        <?php if ($exam_file_exists): 
            $ext = strtolower(pathinfo($exam_file, PATHINFO_EXTENSION));
            $base = basename($exam_file);
        ?>
          <p><strong>Uploaded file:</strong> <?= htmlspecialchars($base) ?></p>

          <?php if (in_array($ext, ['pdf'])): ?>
            <!-- PDF preview -->
            <div class="mb-3">
              <embed src="<?= htmlspecialchars($exam_file) ?>" type="application/pdf" class="embed-preview" />
            </div>
            <a href="<?= htmlspecialchars($exam_file) ?>" target="_blank" class="btn btn-outline-primary">Open in new tab</a>
            <a href="<?= htmlspecialchars($exam_file) ?>" class="btn btn-primary" download>Download</a>

          <?php elseif (in_array($ext, ['jpg','jpeg','png','gif','webp'])): ?>
            <!-- Image preview -->
            <div class="mb-3">
              <img src="<?= htmlspecialchars($exam_file) ?>" class="img-preview" alt="<?= htmlspecialchars($base) ?>">
            </div>
            <a href="<?= htmlspecialchars($exam_file) ?>" target="_blank" class="btn btn-outline-primary">Open full image</a>
            <a href="<?= htmlspecialchars($exam_file) ?>" class="btn btn-primary" download>Download</a>

          <?php else: ?>
            <!-- Other file types -->
            <p>📎 File type not previewable. <a href="<?= htmlspecialchars($exam_file) ?>" target="_blank">Open / Download</a></p>
          <?php endif; ?>

          <p class="mt-3"><small class="text-muted">Note: Kung hindi mo makita ang file, i-refresh ang page o i-contact ang admin.</small></p>

        <?php else: ?>
          <p class="text-muted">Walang na-upload na exam/test result pa. Kung ikaw ay nag-a-upload, hintayin munang ma-verify o i-update ng admin. Kung ang admin ang dapat mag-upload, pakipaalam sa kanila para ma-attach ang file.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
