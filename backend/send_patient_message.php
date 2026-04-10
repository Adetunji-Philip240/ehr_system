<?php
require_once "db.php";

$patient_id = $_POST['patient_id'];
$message = $_POST['message'];

// Insert message for patient
$sql = $conn->prepare("INSERT INTO patient_messages (patient_id, message) VALUES (?, ?)");
$sql->bind_param("is", $patient_id, $message);

$sql->execute();

// Redirect back
header("Location: ../admin/patient_profile.php?id=$patient_id");
exit();