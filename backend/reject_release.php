<?php
require_once "db.php";
session_start();

$id = $_GET['id'];
$admin_id = $_SESSION['user_id'];

$conn->query("
  UPDATE release_requests 
  SET status='Rejected', updated_at=NOW(), action_by=$admin_id
  WHERE id=$id
");

header("Location: ../admin/release_requests.php");