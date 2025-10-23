<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $applicant_id = intval($_POST['applicant_id'] ?? 0);

    if ($applicant_id > 0) {
        $query = "UPDATE applicants SET status='Scholar' WHERE id=$applicant_id";
        if (mysqli_query($conn, $query)) {
            echo "🎉 Applicant has been approved as a Scholar!";
        } else {
            echo "❌ Database update failed: " . mysqli_error($conn);
        }
    } else {
        echo "⚠️ Invalid applicant.";
    }
}
?>
