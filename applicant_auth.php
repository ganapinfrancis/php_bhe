<?php
session_start();
include 'db_connect.php';

$message = "";

// LOGIN
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM applicants WHERE email = '$email'");

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            $_SESSION['applicant_email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['applicant_id'] = $user['applicant_id'];
            header("Location: applicant_dashboard.php");
            exit;
        } else {
            $message = "<div class='alert alert-danger'>❌ Incorrect password!</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>❌ No account found with that email!</div>";
    }
}

// REGISTER
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = mysqli_query($conn, "SELECT * FROM applicants WHERE email = '$email'");
    if (mysqli_num_rows($check) > 0) {
        $message = "<div class='alert alert-warning'>⚠️ Email already exists!</div>";
    } else {
        $query = "INSERT INTO applicants (full_name, email, password) VALUES ('$full_name', '$email', '$password')";
        if (mysqli_query($conn, $query)) {
            $newUser = mysqli_query($conn, "SELECT * FROM applicants WHERE email = '$email'");
            $user = mysqli_fetch_assoc($newUser);

            $_SESSION['applicant_email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['applicant_id'] = $user['applicant_id'];

            header("Location: applicant_dashboard.php");
            exit;
        } else {
            $message = "<div class='alert alert-danger'>❌ Error: " . mysqli_error($conn) . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Applicant Login/Register</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.
