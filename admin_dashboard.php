<?php
include 'db_connect.php';


$message = '';


// =============================
// ANNOUNCEMENTS: CREATE
// =============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_announcement'])) {
  $title    = mysqli_real_escape_string($conn, $_POST['title'] ?? '');
  $content  = mysqli_real_escape_string($conn, $_POST['content'] ?? '');
  $audience = mysqli_real_escape_string($conn, $_POST['audience'] ?? 'All');

  
  $target_user_type = $_POST['target_user_type'] ?? NULL;
  $target_user_id   = !empty($_POST['target_user_id']) ? (int)$_POST['target_user_id'] : NULL;

  if ($title === '' || $content === '') {
    $message .= "<div class='alert alert-danger'>❌ Title and Content are required.</div>";
  } else {
    $created_by = 'Admin';
    $ins = "INSERT INTO announcements (title, content, audience, created_by, target_user_type, target_user_id)
            VALUES (
              '$title', 
              '$content', 
              '$audience', 
              '$created_by',
              " . ($target_user_type ? "'$target_user_type'" : "NULL") . ",
              " . ($target_user_id ? $target_user_id : "NULL") . "
            )";

    if (mysqli_query($conn, $ins)) {
      $message .= "<div class='alert alert-success'>✅ Announcement published.</div>";
    } else {
      $message .= "<div class='alert alert-danger'>❌ DB Error: " . htmlspecialchars(mysqli_error($conn)) . "</div>";
    }
  }
}

// =============================
// ANNOUNCEMENTS: DELETE
// =============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_announcement'])) {
  $announcement_id = (int)($_POST['announcement_id'] ?? 0);
  if ($announcement_id > 0) {
    $del = "DELETE FROM announcements WHERE announcement_id = $announcement_id";
    if (mysqli_query($conn, $del)) {
      $message .= "<div class='alert alert-success'>🗑️ Announcement deleted.</div>";
    } else {
      $message .= "<div class='alert alert-danger'>❌ DB Error: " . htmlspecialchars(mysqli_error($conn)) . "</div>";
    }
  }
}

// =============================
// PRIVATE MESSAGE: SEND
// =============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_private_message'])) {
  echo "<pre>";($_POST); echo "</pre>";
  $subject = mysqli_real_escape_string($conn, $_POST['subject']);
  $message_text = mysqli_real_escape_string($conn, $_POST['message']);
  $target_user_type = $_POST['target_user_type'];
  $target_user_id = (int) $_POST['target_user_id'];

  if ($subject && $message_text && $target_user_type && $target_user_id) {
    $sender = "Admin";
    $ins = "INSERT INTO private_messages (sender, target_user_type, target_user_id, subject, message)
            VALUES ('$sender', '$target_user_type', $target_user_id, '$subject', '$message_text')";
    if (mysqli_query($conn, $ins)) {
      $message .= "<div class='alert alert-success'>✅ Private message sent successfully.</div>";
    } else {
      $message .= "<div class='alert alert-danger'>❌ Error sending message: " . htmlspecialchars(mysqli_error($conn)) . "</div>";
    }
  } else {
    $message .= "<div class='alert alert-danger'>❌ Please select a user and write a message.</div>";
  }
}

// =============================
// APPLICANT: UPDATE
// =============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_applicant'])) {
  $applicant_id     = mysqli_real_escape_string($conn, $_POST['applicant_id']);
  $exam_date        = mysqli_real_escape_string($conn, $_POST['exam_date']);
  $interview_date   = mysqli_real_escape_string($conn, $_POST['interview_date']);
  $exam_result      = mysqli_real_escape_string($conn, $_POST['exam_result']);
  $interview_result = mysqli_real_escape_string($conn, $_POST['interview_result']);
  $scholar_status   = mysqli_real_escape_string($conn, $_POST['scholar_status']);

  $update = "UPDATE applicants SET 
    exam_date='$exam_date', 
    interview_date='$interview_date', 
    exam_result='$exam_result', 
    interview_result='$interview_result',
    scholar_status='$scholar_status'
    WHERE applicant_id='$applicant_id'";

  if (mysqli_query($conn, $update)) {
    $message .= "<div class='alert alert-success'>✅ Applicant data updated successfully.</div>";
  } else {
    $message .= "<div class='alert alert-danger'>❌ Error: " . htmlspecialchars(mysqli_error($conn)) . "</div>";
  }
}



// =============================
// SCHOLAR: UPDATE (purok/activity/status)
// =============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_scholar'])) {
  $scholar_id        = mysqli_real_escape_string($conn, $_POST['scholar_id']);
  $purok             = mysqli_real_escape_string($conn, $_POST['purok']);
  $activity          = mysqli_real_escape_string($conn, $_POST['activity_participation']);
  $status            = mysqli_real_escape_string($conn, $_POST['status']);
  $scholar_standing = mysqli_real_escape_string($conn, $_POST['scholar_standing']);

  $update_scholar = "UPDATE scholars SET 
    purok='$purok', 
    activity_participation='$activity',
    status='$status',
    scholar_standing='$scholar_standing'
    WHERE scholar_id='$scholar_id'";

    

  if (mysqli_query($conn, $update_scholar)) {
    $message .= "<div class='alert alert-success'>✅ Scholar data updated successfully.</div>";
  } else {
    $message .= "<div class='alert alert-danger'>❌ Error: " . htmlspecialchars(mysqli_error($conn)) . "</div>";
  }
}

// =============================
// APPLICANT: DELETE
// =============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_applicant'])) {
  $applicant_id = (int)($_POST['applicant_id'] ?? 0);
  if ($applicant_id > 0) {
    $del = "DELETE FROM applicants WHERE applicant_id = $applicant_id";
    if (mysqli_query($conn, $del)) {
      $message .= "<div class='alert alert-success'>🗑️ Applicant deleted.</div>";
    } else {
      $message .= "<div class='alert alert-danger'>❌ Error deleting applicant: " . htmlspecialchars(mysqli_error($conn)) . "</div>";
    }
  }
}


// =============================
// FETCH DATA
// =============================
$applicants    = mysqli_query($conn, "SELECT * FROM applicants ORDER BY applicant_id DESC");
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

$announcements = mysqli_query($conn, "SELECT * FROM announcements ORDER BY created_at DESC");
$all_scholars   = mysqli_query($conn, "SELECT scholar_id, full_name FROM scholars ORDER BY full_name ASC");
$all_applicants = mysqli_query($conn, "SELECT applicant_id, full_name FROM applicants ORDER BY full_name ASC");

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
  <meta charset="UTF-8" />
  <title>Admin Dashboard | Smart Scholar</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>



  </div>

  <?php if ($message): ?>
    <?= $message ?>
  <?php endif; ?>

  

  <!-- ============ Announcements: Create ============ -->
<!--
<div class="card mb-4">
  ... laman ng Create Announcement ...
</div>
-->

<!-- ============ Private Message: Create ============ -->
<!--
<div class="card mb-4">
  ... laman ng Private Message ...
</div>
-->

  <!-- ============ Announcements: List ============ -->
<!--
<div class="card mb-4">
  ... laman ng Recent Announcements ...
</div>
-->
  
  <!-- ============ Applicants Table ============ -->
<!--
<div class="card mb-4">
  ... laman ng Applicant Evaluation ...
</div>
-->

  <!-- ============ Scholars Table ============ -->
<!--
<div class="card">
  ... laman ng Scholar Participation Monitoring ...
</div>
-->