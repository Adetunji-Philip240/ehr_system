<?php
session_start();
require_once "db.php";

$user_id = $_SESSION['user_id'];

$patient = $conn->query("
    SELECT id 
    FROM patients 
    WHERE user_id='$user_id'
")->fetch_assoc();

$patient_id = $patient['id'];

// =========================
// Assignments
// =========================
$assignments = $conn->query("
    SELECT COUNT(*) as total
    FROM assignments
    WHERE patient_id='$patient_id'
")->fetch_assoc()['total'];

// =========================
// Records
// =========================
$records = $conn->query("
    SELECT COUNT(*) as total
    FROM records
    WHERE patient_id='$patient_id'
")->fetch_assoc()['total'];

echo json_encode([
  "assignments" => $assignments,
  "records" => $records
]);
