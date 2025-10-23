<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $grades = $_POST['grades'] ?? '';
    $exam   = $_POST['exam_score'] ?? '';
    $inter  = $_POST['interview_score'] ?? '';

    // Path to python
    $python = "python"; // try "python3" if hindi gumana
    $cmd = "$python ml_model/predict.py " . escapeshellarg($grades) . " " . escapeshellarg($exam) . " " . escapeshellarg($inter);

    // Run command
    $output = shell_exec($cmd . " 2>&1"); // add error output
    if ($output === null) {
        echo "<p style='color:red;'>❌ Error: predict.py did not run.</p>";
    } else {
        echo "<h2>📊 AI Evaluation Result</h2>";
        echo "<p><strong>Grades:</strong> $grades</p>";
        echo "<p><strong>Exam Score:</strong> $exam</p>";
        echo "<p><strong>Interview Score:</strong> $inter</p>";
        echo "<hr>";
        echo "<h3>✅ System Decision:
