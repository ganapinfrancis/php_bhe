<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['applicant_id'])) {
    $applicant_id = (int)$_POST['applicant_id'];

    // target folder
    $target_dir = "results/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_name = time() . "_" . basename($_FILES["result_image"]["name"]);
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES["result_image"]["tmp_name"], $target_file)) {
        mysqli_query($conn, "UPDATE applicants SET result_image='" . mysqli_real_escape_string($conn, $target_file) . "' WHERE applicant_id=$applicant_id");
        header("Location: admin_dashboard.php?msg=Upload+success");
    } else {
        header("Location: admin_dashboard.php?msg=Upload+failed");
    }
} else {
    header("Location: admin_dashboard.php?msg=Invalid+request");
}


exit;
?>
