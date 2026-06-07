<?php
session_start();
require_once "db.php";

// ==========================
// GET FORM DATA
// ==========================
$full_name  = $_POST['full_name'];
$dob        = $_POST['dob'];
$address    = $_POST['address'];
$phone      = $_POST['phone'];
$email      = $_POST['email'];
$gender     = $_POST['gender'];

// ==========================
// GENERATE USERNAME (from full name)
// ==========================
$base_username = strtolower(str_replace(" ", "", $full_name));
$random_digits = rand(100, 999);
$username = $base_username . $random_digits;

// ==========================
// GENERATE PASSWORD (plain)
// ==========================
$plain_password = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 8);

// hash password for DB
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

// ==========================
// GENERATE PATIENT CODE
// ==========================
$patient_code = "PAT" . rand(1000, 9999);

// ==========================
// INSERT INTO USERS TABLE
// ==========================
$user_sql = $conn->prepare("
    INSERT INTO users (username, password, role)
    VALUES (?, ?, 'Patient')
");

$user_sql->bind_param("ss", $username, $hashed_password);
$user_sql->execute();

// get user id
$user_id = $conn->insert_id;

// ==========================
// INSERT INTO PATIENTS TABLE
// ==========================
$patient_sql = $conn->prepare("
    INSERT INTO patients 
    (patient_code, full_name, dob, address, phone, email, gender, user_id, date_added)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
");

$patient_sql->bind_param(
  "sssssssi",
  $patient_code,
  $full_name,
  $dob,
  $address,
  $phone,
  $email,
  $gender,
  $user_id
);

$patient_sql->execute();

// ==========================
// STORE TOAST DATA
// ==========================
$_SESSION['patient_created'] = [
  'username' => $username,
  'password' => $plain_password,
  'patient_code' => $patient_code
];

// ==========================
// REDIRECT BACK
// ==========================
header("Location: ../admin/add_patients.php");
exit();
