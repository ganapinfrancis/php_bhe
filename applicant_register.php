<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email     = mysqli_real_escape_string($conn, $_POST['email']);
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $age       = (int)$_POST['age'];
    $purok     = mysqli_real_escape_string($conn, $_POST['purok']);
    $number_of_dependents = (int)$_POST['number_of_dependents'];
    $parent_employment_status = mysqli_real_escape_string($conn, $_POST['parent_employment_status']);

    // Check if email exists
    $check = mysqli_query($conn, "SELECT * FROM applicants WHERE email = '$email'");
    if (mysqli_num_rows($check) > 0) {
        echo "Email already exists!";
        exit;
    }

    // Insert new applicant (with extra fields)
    $query = "INSERT INTO applicants 
        (full_name, email, password, age, purok, number_of_dependents, parent_employment_status) 
        VALUES 
        ('$full_name', '$email', '$password', $age, '$purok', $number_of_dependents, '$parent_employment_status')";

    if (mysqli_query($conn, $query)) {
        // Fetch new user
        $newUser = mysqli_query($conn, "SELECT * FROM applicants WHERE email = '$email'");
        $user = mysqli_fetch_assoc($newUser);

        // Save to session
        $_SESSION['applicant_email']          = $user['email'];
        $_SESSION['full_name']                = $user['full_name'];
        $_SESSION['applicant_id']             = $user['applicant_id'];
        $_SESSION['age']                      = $user['age'];
        $_SESSION['purok']                    = $user['purok'];
        $_SESSION['number_of_dependents']     = $user['number_of_dependents'];
        $_SESSION['parent_employment_status'] = $user['parent_employment_status'];

        // Redirect to dashboard
        header("Location: applicant_dashboard.php");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
