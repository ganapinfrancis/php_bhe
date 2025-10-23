<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['applicant_id'])) {
    $applicant_id = (int)$_POST['applicant_id'];

    // Kunin current image path
    $res = mysqli_query($conn, "SELECT result_image FROM applicants WHERE applicant_id = $applicant_id");
    $row = mysqli_fetch_assoc($res);

    if ($row && !empty($row['result_image'])) {
        $file_path = $row['result_image'];

        // erase ito ang file kung existing
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Clear sa DB
        mysqli_query($conn, "UPDATE applicants SET result_image = '' WHERE applicant_id = $applicant_id");
    }
}

// Balik sa admin dashboard
header("Location: admin_dashboard.php?msg=Result+deleted");
exit;
?>
