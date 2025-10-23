<?php
$grades = $_POST['grades'];        // GPA or GWA
$exam   = $_POST['exam_score'];    
$inter  = $_POST['interview_score'];

$command = escapeshellcmd("python ml_model/predict.py $grades $exam $inter");
$output = shell_exec($command);

echo "Prediction: " . htmlspecialchars($output);
?>
