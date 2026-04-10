<?php
require_once "db.php";

$doctor_id = $_POST['doctor_id'];
$patient_id = $_POST['patient_id'];

// Prevent duplicate active assignment
$check = $conn->query("
SELECT * FROM assignments 
WHERE patient_id=$patient_id AND status='Active'
");

if ($check->num_rows > 0) {
  echo "<script>alert('Patient already assigned!'); window.history.back();</script>";
  exit();
}

// Insert new assignment
$stmt = $conn->prepare("
INSERT INTO assignments (doctor_id, patient_id, status, assigned_date) 
VALUES (?, ?, 'Active', NOW())
");

$stmt->bind_param("ii", $doctor_id, $patient_id);
$stmt->execute();



header("Location: ../admin/doctor_profile.php?id=$doctor_id");
