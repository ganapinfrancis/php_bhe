<?php
include '../db_connect.php';

$result = null;
$probability = null;
$criteria = null;
$source = null;
$error = "";

// Determine applicant ID: GET (from dashboard) or POST (from manual form)
$applicantId = $_GET['applicant_id'] ?? ($_POST['applicant_id'] ?? null);

if ($applicantId !== null) {
    // Try to fetch applicant details from DB
    $applicant = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM applicants WHERE applicant_id=".(int)$applicantId));
    
    if (!$applicant) {
        $error = "⚠️ Applicant not found!";
    } else {
        // Decide input values: from DB (automatic) or POST (manual)
        $gpa = isset($_POST['gpa']) ? floatval($_POST['gpa']) : floatval($applicant['gwa'] ?? 0);
        $exam = isset($_POST['exam_results']) ? floatval($_POST['exam_results']) : floatval($applicant['exam_result'] ?? 0);
        $dependents = isset($_POST['number_of_dependents']) ? intval($_POST['number_of_dependents']) : intval($applicant['number_of_dependents'] ?? 0);
        $employment = isset($_POST['parent_employment_status']) ? strtolower(trim($_POST['parent_employment_status'])) : strtolower($applicant['parent_employment_status'] ?? '');

        if ($gpa === null || $exam === null || $dependents === null || $employment === null || $employment === '') {
            $error = "⚠️ Missing applicant data!";
        } else {
            // Payload for ML model
            $payload = array(
                "gpa" => $gpa,
                "exam_results" => $exam,
                "number_of_dependents" => $dependents,
                "parent_employment_status" => $employment,
                "use_rule" => "always"
                      );

          $ch = curl_init("https://flaskapi-production-77a5.up.railway.app/predict");
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_POST, true);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
          curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // <-- add this
          $response = curl_exec($ch);
          curl_close($ch);

          if ($response === false) {
              $error = curl_error($ch); // get the actual error
              echo "❌ cURL Error: $error";
            } else {
                $decoded = json_decode($response, true);
                if ($decoded !== null && isset($decoded['status']) && $decoded['status'] === 'success') {
                    $result = $decoded['result'] ?? ($decoded['prediction'] ?? null);
                    $probability = $decoded['probability'] ?? null;
                    $criteria = $decoded['criteria'] ?? null;
                    $source = $decoded['source'] ?? null;

                    if (is_numeric($probability)) {
                        $probability = round(floatval($probability) * 100, 2) . '%';
                    }

                    // ✅ Update scholar_status automatically
                    mysqli_query($conn, "UPDATE applicants SET scholar_status='$result' WHERE applicant_id=".(int)$applicantId);
                } else {
                    $error = "Unexpected response from ML server: ".htmlspecialchars($response);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Scholarship Eligibility Checker</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; }
.container-box { max-width: 600px; margin: 40px auto; background: #fff; padding: 28px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.result-box { margin-top: 18px; padding: 14px; border-radius: 8px; }
.result-eligible { background: #e6ffed; border: 1px solid #00b74a; }
.result-noteligible { background: #ffe6e6; border: 1px solid #d9534f; }
.error-box { margin-top: 18px; padding: 14px; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 8px; }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Smart Scholar System</a>
    <div class="d-flex">
      <a href="../admin_dashboard.php" class="btn btn-light btn-sm">⬅ Back to Dashboard</a>
    </div>
  </div>
</nav>

<div class="container-box">
<h3 class="text-center text-primary mb-4">Scholarship Eligibility Checker</h3>

<?php if (!empty($error)): ?>
  <div class="error-box"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($result !== null): ?>
  <div class="result-box <?php echo ($result === 'Eligible') ? 'result-eligible' : 'result-noteligible'; ?>">
    <h4 class="mb-2"><?php echo htmlspecialchars($result); ?></h4>
    <?php if ($probability !== null): ?>
      <p><strong>Criteria:</strong> <em><?php echo htmlspecialchars($criteria); ?></em></p>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($applicantId === null): ?>
  <!-- Show manual form only if no applicant_id -->
  <form method="POST" class="mb-3">
    <!-- same form as before -->
  </form>
<?php endif; ?>
</div>
</body>
</html>
