<?php
require_once "db.php";

$doctor_id = $_GET['doctor_id'];
$patient_id = $_GET['patient_id'];

$stmt = $conn->prepare("
UPDATE assignments 
SET status='Released', released_date=NOW()
WHERE doctor_id=? AND patient_id=? AND status='Active'
");

$check = $conn->query("
SELECT * FROM assignments 
WHERE doctor_id=$doctor_id AND patient_id=$patient_id AND status='Active'
");

if ($check->num_rows == 0) {
  die("Already released");
}

$stmt->bind_param("ii", $doctor_id, $patient_id);
$stmt->execute();

header("Location: ../admin/doctor_profile.php?id=$doctor_id");