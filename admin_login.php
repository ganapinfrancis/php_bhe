<?php
session_start();

// Fixed admin credentials
$admin_username = "admin";
$admin_password = "admin1234";

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get form inputs
    $username = $_POST["username"] ?? '';
    $password = $_POST["password"] ?? '';

    // Check credentials
    if ($username === $admin_username && $password === $admin_password) {
        $_SESSION["admin_logged_in"] = true;
        header("Location: admin_dashboard.php");
        exit();
    } else {
        // Wrong credentials: redirect back with alert
        echo "<script>alert('Invalid username or password'); window.location.href='index.html';</script>";
    }
} else {
    // Prevent direct GET access
    echo "Invalid request method.";
}
?>
