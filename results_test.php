<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applicant_id = (int)$_POST['applicant_id'];
    $target_dir = "uploads/results/";
    
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true); // auto-create folder kung wala pa
    }

    $file_name = time() . "_" . basename($_FILES["result_image"]["name"]);
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES["result_image"]["tmp_name"], $target_file)) {
        $sql = "UPDATE applicants SET result_image = '$target_file' WHERE applicant_id = $applicant_id";
        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('✅ File uploaded successfully!'); window.history.back();</script>";
        } else {
            echo "<script>alert('❌ Database update failed: " . mysqli_error($conn) . "');</script>";
        }
    } else {
        echo "<script>alert('❌ File upload failed.');</script>";
    }
}
?>
