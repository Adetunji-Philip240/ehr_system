<?php
session_start();
require_once "db.php";

// ==========================
// GET FORM DATA
// ==========================
$full_name   = $_POST['full_name'];
$specialty   = $_POST['specialty'];
$department  = $_POST['department'];
$phone       = $_POST['phone_number'];
$email       = $_POST['email'];
$gender      = $_POST['gender'];

// ==========================
// GENERATE USERNAME
// ==========================
$base_username = strtolower(str_replace(" ", "", $full_name));
$random_digits = rand(100, 999);
$username = $base_username . $random_digits;

// ==========================
// GENERATE PASSWORD (PLAIN)
// ==========================
$plain_password = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 8);

// ==========================
// HASH PASSWORD FOR DB
// ==========================
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

// ==========================
// GENERATE DOCTOR CODE
// ==========================
$doctor_code = "DOC" . rand(1000, 9999);

// ==========================
// INSERT INTO USERS TABLE
// ==========================
$user_sql = $conn->prepare("
    INSERT INTO users (username, password, role)
    VALUES (?, ?, 'Doctor')
");

$user_sql->bind_param("ss", $username, $hashed_password);
$user_sql->execute();

// Get user ID
$user_id = $conn->insert_id;

// ==========================
// INSERT INTO DOCTORS TABLE
// ==========================
$doctor_sql = $conn->prepare("
    INSERT INTO doctors 
    (doctor_code, full_name, specialization, department, phone, email, gender, user_id, date_joined)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
");

$doctor_sql->bind_param(
  "sssssssi",
  $doctor_code,
  $full_name,
  $specialty,
  $department,
  $phone,
  $email,
  $gender,
  $user_id
);

$doctor_sql->execute();

// ==========================
// STORE DATA FOR TOAST
// ==========================
$_SESSION['doctor_created'] = [
  'username' => $username,
  'password' => $plain_password
];

// ==========================
// REDIRECT BACK TO FORM PAGE
// ==========================
header("Location: ../admin/add_doctors.php");
exit();