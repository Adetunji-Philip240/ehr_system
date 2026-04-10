<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.html");
  exit();
}

$user_id = $_SESSION['user_id'];
$patient_id = $_POST['patient_id'];

// Get doctor ID
$stmt = $conn->prepare("SELECT id FROM doctors WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();
$doctor_id = $doctor['id'];

// Prevent duplicate request
$check = $conn->prepare("
  SELECT * FROM release_requests 
  WHERE patient_id=? AND doctor_id=? AND status='Pending'
");
$check->bind_param("ii", $patient_id, $doctor_id);
$check->execute();

if ($check->get_result()->num_rows > 0) {
  $_SESSION['msg'] = "Request already sent!";
} else {
  $stmt = $conn->prepare("
    INSERT INTO release_requests (doctor_id, patient_id)
    VALUES (?, ?)
  ");
  $stmt->bind_param("ii", $doctor_id, $patient_id);
  $stmt->execute();

  $_SESSION['msg'] = "Release request sent to admin!";
}

header("Location: ../doctor/patient_profile.php?id=$patient_id");
exit();