<?php
session_start();
require_once "db.php";


if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Admin') {
  header("Location: ../index.html");
  exit();
}

if (isset($_GET['id'])) {

  $doctor_id = $_GET['id'];

  // Step 1: Get user_id (for deleting login)
  $sql = "SELECT user_id FROM doctors WHERE id=?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $doctor_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $user_id = $row['user_id'];

    // Step 2: Delete doctor
    $deleteDoctor = "DELETE FROM doctors WHERE id=?";
    $stmt1 = $conn->prepare($deleteDoctor);
    $stmt1->bind_param("i", $doctor_id);
    $stmt1->execute();

    // Step 3: Delete login from users table
    $deleteUser = "DELETE FROM users WHERE id=?";
    $stmt2 = $conn->prepare($deleteUser);
    $stmt2->bind_param("i", $user_id);
    $stmt2->execute();

    echo "<script>alert('Doctor deleted successfully'); window.location.href='../admin/doctors.php';</script>";
  } else {
    echo "Doctor not found!";
  }
}