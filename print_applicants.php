<?php
include 'db_connect.php';
$applicants = mysqli_query($conn, "SELECT * FROM applicants ORDER BY applicant_id DESC");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Print Applicants</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    @media print {
      .no-print { display: none; }
    }
  </style>
</head>
<body onload="window.print()">
  <div class="container mt-4">
    <h2 class="mb-3 text-center">List of Applicants</h2>

    <table class="table table-bordered table-sm">
      <thead>
        <tr>
          <th>Name</th>
          <th>Exam Result</th>
          <th>Interview Result</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = mysqli_fetch_assoc($applicants)): ?>
          <tr>
            <td><?= htmlspecialchars($row['full_name']) ?></td>
            <td><?= htmlspecialchars($row['exam_result']) ?></td>
            <td><?= htmlspecialchars($row['interview_result']) ?></td>
            <td><?= htmlspecialchars($row['scholar_status']) ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
