<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['scholar_id'])) {
    $scholar_id = (int)$_POST['scholar_id'];

    // Clear attendance record
    $sql = "UPDATE scholars 
            SET attendance_image=NULL, attendance_date=NULL 
            WHERE scholar_id=$scholar_id";

    if (mysqli_query($conn, $sql)) {
        header("Location: admin_dashboard.php?msg=Attendance+deleted");
        exit;
    } else {
        die("❌ DB Error: " . mysqli_error($conn));
    }
} else {
    header("Location: admin_dashboard.php?msg=Invalid+request");
    exit;
}
?>
