<?php
session_start();
require_once "db.php";

$user_id = $_SESSION['user_id'];

$doctor = $conn->query("
    SELECT id
    FROM doctors
    WHERE user_id='$user_id'
")->fetch_assoc();

$doctor_id = $doctor['id'];

// Active patients
$patients = $conn->query("
    SELECT COUNT(DISTINCT patient_id) as total
    FROM assignments
    WHERE doctor_id='$doctor_id' AND status='Active'
")->fetch_assoc()['total'];

// Records
$records = $conn->query("
    SELECT COUNT(*) as total
    FROM records
    WHERE doctor_id='$doctor_id'
")->fetch_assoc()['total'];

echo json_encode([
    "patients" => $patients,
    "records" => $records
]);
