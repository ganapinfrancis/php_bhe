<?php
session_start();
session_destroy();
header("Location: index.html?message=applicant_logged_out");
exit;
?>
