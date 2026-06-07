<?php
require_once "db.php";
session_start();

$id = $_GET['id'];
$admin_id = $_SESSION['user_id'];

// 1. Get patient + doctor
$request = $conn->query("
  SELECT patient_id, doctor_id 
  FROM release_requests 
  WHERE id=$id
")->fetch_assoc();

$patient_id = $request['patient_id'];
$doctor_id = $request['doctor_id'];

// 2. Count records BEFORE releasing
$count = $conn->query("
  SELECT COUNT(*) as total 
  FROM records 
  WHERE patient_id = $patient_id 
  AND doctor_id = $doctor_id
")->fetch_assoc()['total'];

// 3. Update assignment (SAVE frozen count)
$conn->query("
  UPDATE assignments 
  SET 
    status='Released',
    released_date=NOW(),
    record_count=$count
  WHERE patient_id=$patient_id 
  AND doctor_id=$doctor_id 
  AND status='Active'
");

// 4. Update request
$conn->query("
  UPDATE release_requests 
  SET status='Approved', updated_at=NOW(), action_by=$admin_id
  WHERE id=$id
");

header("Location: ../admin/release_requests.php");
exit();
