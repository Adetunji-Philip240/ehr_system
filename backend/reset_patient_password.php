<?php
session_start();
require_once "db.php";

// =========================
// GET DATA
// =========================
$patient_id = $_POST['patient_id'];
$admin_code = $_POST['admin_code'];

// =========================
// ADMIN PASSCODE
// =========================
$correct_code = "1234";

if ($admin_code !== $correct_code) {
  $_SESSION['reset_error'] = "Invalid admin passcode!";
  header("Location: ../admin/patient_profile.php?id=$patient_id");
  exit();
}

// =========================
// GENERATE NEW PASSWORD
// =========================
$new_password = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ23456789"), 0, 8);
$hashed = password_hash($new_password, PASSWORD_DEFAULT);

// =========================
// GET USER ID FROM PATIENT TABLE
// =========================
$stmt = $conn->prepare("SELECT user_id FROM patients WHERE id=?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$user_id = $result->fetch_assoc()['user_id'];

// =========================
// UPDATE USERS TABLE PASSWORD
// =========================
$update = $conn->prepare("UPDATE users SET password=? WHERE id=?");
$update->bind_param("si", $hashed, $user_id);
$update->execute();

// =========================
// SEND TO TOAST
// =========================
$_SESSION['patient_reset'] = [
  'password' => $new_password
];

// =========================
// REDIRECT BACK
// =========================
header("Location: ../admin/patient_profile.php?id=$patient_id");
exit();