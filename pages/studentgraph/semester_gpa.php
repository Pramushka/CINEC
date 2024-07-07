<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../../Login_S.php");
    exit();
}

// Include the database helper file
require_once '../../db.helper.php';

// Fetch the marks for the logged-in student
$student_id = $_SESSION['student_id'];
$studentMarksSql = "SELECT m.ID as MODULE_ID, m.NAME as MODULE_NAME, m.CREDIT, m.Semester, mk.MARKS
                    FROM markes mk
                    JOIN module m ON mk.MODULE_ID = m.ID
                    WHERE mk.STUDENT_TABLE_ID = ?";
$studentMarksStmt = $conn->prepare($studentMarksSql);
$studentMarksStmt->bind_param("i", $student_id);
$studentMarksStmt->execute();
$studentMarksResult = $studentMarksStmt->get_result();

$studentMarks = [];
while ($row = $studentMarksResult->fetch_assoc()) {
    $studentMarks[] = $row;
}

$semesters = [];

// Grading scale for numerical marks
function convertMarksToGradePoints($marks) {
    if ($marks >= 85) return 4.0;
    if ($marks >= 70) return 4.0;
    if ($marks >= 65) return 3.7;
    if ($marks >= 60) return 3.3;
    if ($marks >= 55) return 3.0;
    if ($marks >= 50) return 2.7;
    if ($marks >= 45) return 2.3;
    if ($marks >= 40) return 2.0;
    if ($marks >= 35) return 1.7;
    if ($marks >= 30) return 1.3;
    if ($marks >= 25) return 1.0;
    return 0.0;
}

foreach ($studentMarks as $mark) {
    $semester = $mark['Semester'];
    $credit = $mark['CREDIT'];
    $numericalMarks = $mark['MARKS'];
    $gp = convertMarksToGradePoints($numericalMarks);

    if (!isset($semesters[$semester])) {
        $semesters[$semester] = ['totalPoints' => 0, 'totalCredits' => 0];
    }

    $semesters[$semester]['totalPoints'] += $gp * $credit;
    $semesters[$semester]['totalCredits'] += $credit;
}

$sgpaData = [];
foreach ($semesters as $semester => $data) {
    $sgpa = $data['totalPoints'] / $data['totalCredits'];
    $sgpaData[] = ['semester' => 'Semester ' . $semester, 'sgpa' => round($sgpa, 2)];
}

// Prepare data for Chart.js
$labels = [];
$sgpaValues = [];
foreach ($sgpaData as $data) {
    $labels[] = $data['semester'];
    $sgpaValues[] = $data['sgpa'];
}

$studentMarksStmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Student Semester GPA</title>
    <link rel="stylesheet" href="../../vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../../vendors/base/vendor.bundle.base.css">
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.css">
    <link rel="shortcut icon" href="../../images/favicon.png" />
</head>
<body>
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-stretch auth auth-img-bg">
                <div class="row flex-grow">
                    <div class="col-lg-12 d-flex align-items-center justify-content-center">
                        <div class="auth-form-transparent text-left p-3">
                            <div class="brand-logo"></div>
                            <h4>Semester GPA Line Graph</h4>
                            <canvas id="semesterGPAChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../../vendors/base/vendor.bundle.base.js"></script>
    <script src="../../js/off-canvas.js"></script>
    <script src="../../js/hoverable-collapse.js"></script>
    <script src="../../js/template.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var ctx = document.getElementById('semesterGPAChart').getContext('2d');
            var semesterGPAChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($labels); ?>,
                    datasets: [{
                        label: 'SGPA',
                        data: <?php echo json_encode($sgpaValues); ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        fill: false // Ensures the line graph is not filled below the line
                    }]
                },
                options: {
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                suggestedMax: 4
                            }
                        }]
                    }
                }
            });
        });
    </script>
</body>
</html>
