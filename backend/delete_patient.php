<?php
session_start();

require_once "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Admin') {
  header("Location: ../index.html");
  exit();
}

if (isset($_GET['id'])) {

  $patient_id = $_GET['id'];

  // Step 1: Get user_id (to delete login too)
  $sql = "SELECT user_id FROM patients WHERE id=?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $patient_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $user_id = $row['user_id'];

    // Step 2: Delete patient
    $deletePatient = "DELETE FROM patients WHERE id=?";
    $stmt1 = $conn->prepare($deletePatient);
    $stmt1->bind_param("i", $patient_id);
    $stmt1->execute();

    // Step 3: Delete user login
    $deleteUser = "DELETE FROM users WHERE id=?";
    $stmt2 = $conn->prepare($deleteUser);
    $stmt2->bind_param("i", $user_id);
    $stmt2->execute();

    echo "<script>alert('Patient deleted successfully'); window.location.href='../admin/patients.php';</script>";
  } else {
    echo "Patient not found!";
  }
}