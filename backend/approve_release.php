<?php
require_once "db.php";
session_start();

$id = $_GET['id'];
$admin_id = $_SESSION['user_id'];

// 1. Update request
$conn->query("
  UPDATE release_requests 
  SET status='Approved', updated_at=NOW(), action_by=$admin_id
  WHERE id=$id
");

// 2. Release patient
$conn->query("
  UPDATE assignments 
  SET status='Released', released_date=NOW()
  WHERE patient_id = (
    SELECT patient_id FROM release_requests WHERE id=$id
  )
");

header("Location: ../admin/release_requests.php");