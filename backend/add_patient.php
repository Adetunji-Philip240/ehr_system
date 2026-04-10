<?php
session_start();
require_once "db.php";

// =======================
// GET FORM DATA
// =======================
$full_name = $_POST['full_name'];
$address = $_POST['address'];
$dob = $_POST['dob'];
$phone = $_POST['phone_number'];
$email = $_POST['email'];
$gender = $_POST['gender'];

// =======================
// GENERATE LOGIN DETAILS
// =======================
$username = "PAT" . rand(1000, 9999);
$plain_password = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ23456789"), 0, 8);
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

// =======================
// 1. INSERT INTO USERS TABLE
// =======================
$role = "Patient";

$stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $hashed_password, $role);
$stmt->execute();

$user_id = $stmt->insert_id;

// =======================
// 2. INSERT INTO PATIENTS TABLE
// =======================
$stmt2 = $conn->prepare("INSERT INTO patients (user_id, full_name, address, dob, phone, email, gender) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt2->bind_param("issssss", $user_id, $full_name, $address, $dob, $phone, $email, $gender);
$stmt2->execute();

// =======================
// TOAST DATA
// =======================
$_SESSION['patient_created'] = [
  'username' => $username,
  'password' => $plain_password
];

// =======================
// REDIRECT
// =======================
header("Location: ../admin/add_patients.php");
exit();