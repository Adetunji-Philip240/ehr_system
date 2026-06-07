<?php
session_start();
require_once "db.php";

// =========================
// SECURITY CHECK
// =========================
if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.html");
  exit();
}

// =========================
// GET VALUES
// =========================
$doctor_id = $_GET['doctor_id'] ?? 0;
$patient_id = $_GET['patient_id'] ?? 0;

// =========================
// VALIDATE INPUT
// =========================
if (!$doctor_id || !$patient_id) {
  die("Invalid request.");
}

// =========================
// CHECK IF STILL ACTIVE
// =========================
$stmt = $conn->prepare("
  SELECT * FROM assignments 
  WHERE doctor_id=? AND patient_id=? AND status='Active'
");
$stmt->bind_param("ii", $doctor_id, $patient_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
  die("Assignment already released.");
}

// =========================
// COUNT RECORDS (FREEZE VALUE)
// =========================
$stmt = $conn->prepare("
  SELECT COUNT(*) as total 
  FROM records 
  WHERE patient_id=? AND doctor_id=?
");
$stmt->bind_param("ii", $patient_id, $doctor_id);
$stmt->execute();
$count = $stmt->get_result()->fetch_assoc()['total'];

// =========================
// UPDATE ASSIGNMENT
// =========================
$stmt = $conn->prepare("
  UPDATE assignments 
  SET 
    status='Released',
    released_date=NOW(),
    record_count=?
  WHERE doctor_id=? AND patient_id=? AND status='Active'
");

$stmt->bind_param("iii", $count, $doctor_id, $patient_id);
$stmt->execute();

// =========================
// SUCCESS MESSAGE (OPTIONAL)
// =========================
$_SESSION['msg'] = "Doctor successfully released from patient.";

// =========================
// REDIRECT
// =========================
header("Location: ../admin/doctor_profile.php?id=" . $doctor_id);
exit();
