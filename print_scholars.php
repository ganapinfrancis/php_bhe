<?php
include 'db_connect.php';
$scholars = mysqli_query($conn, "SELECT * FROM scholars ORDER BY scholar_id DESC");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Print Scholars</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    @media print {
      .no-print { display: none; }
    }
  </style>
</head>
<body onload="window.print()">
  <div class="container mt-4">
    <h2 class="mb-3 text-center">List of Scholars</h2>

    <table class="table table-bordered table-sm">
      <thead>
        <tr>
          <th>Name</th>
          <th>Purok</th>
          <th>Activity Participation</th>
          <th>Status</th>
          <th>Scholar Standing</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = mysqli_fetch_assoc($scholars)): ?>
          <tr>
            <td><?= htmlspecialchars($row['full_name']) ?></td>
            <td><?= htmlspecialchars($row['purok']) ?></td>
            <td><?= htmlspecialchars($row['activity_participation']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td><?= htmlspecialchars($row['scholar_standing']) ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
