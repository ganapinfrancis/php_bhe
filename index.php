<?php
// index.php
include 'navbar.php';

// Determine which page to load
$page = $_GET['page'] ?? 'home';

switch($page){
    case 'home':
        include 'dashboard_home.php';
        break;
    case 'announcements':
        include 'announcements.php';
        break;
    case 'applicants':
        include 'dashboard_applicant_admin.php';
        break;
    case 'scholars':
        include 'admin_scholars.php';
        break;
    case 'messages':
        include 'private_messages.php';
        break;
    default:
        echo "<h2>Page not found!</h2>";
}
?>
