<?php
require_once "db.php";

$doctor_id = $_POST['doctor_id'];
$message = $_POST['message'];

$sql = $conn->prepare("INSERT INTO messages (doctor_id, message) VALUES (?, ?)");
$sql->bind_param("is", $doctor_id, $message);
$sql->execute();

header("Location: ../admin/doctor_profile.php?id=$doctor_id");