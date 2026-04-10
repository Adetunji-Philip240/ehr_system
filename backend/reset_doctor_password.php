<?php
session_start();
require_once "db.php";

// =========================
// GET DATA
// =========================
$doctor_id = $_POST['doctor_id'];
$admin_code = $_POST['admin_code'];

// =========================
// SET YOUR ADMIN PASSCODE HERE
// =========================
$correct_code = "1234"; // CHANGE THIS 🔐

if ($admin_code !== $correct_code) {
  $_SESSION['reset_error'] = "Invalid admin passcode!";
  header("Location: ../admin/doctor_profile.php?id=$doctor_id");
  exit();
}

// =========================
// GENERATE NEW PASSWORD
// =========================
$new_password = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ23456789"), 0, 8);
$hashed = password_hash($new_password, PASSWORD_DEFAULT);

// =========================
// GET USER ID
// =========================
$stmt = $conn->prepare("SELECT user_id FROM doctors WHERE id=?");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$user_id = $stmt->get_result()->fetch_assoc()['user_id'];

// =========================
// UPDATE PASSWORD
// =========================
$update = $conn->prepare("UPDATE users SET password=? WHERE id=?");
$update->bind_param("si", $hashed, $user_id);
$update->execute();

// =========================
// SEND TO TOAST
// =========================
$_SESSION['password_reset'] = [
  'password' => $new_password
];

// =========================
// REDIRECT BACK
// =========================
header("Location: ../admin/doctor_profile.php?id=$doctor_id");
exit();