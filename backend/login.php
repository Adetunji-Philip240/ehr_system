<?php
session_start();
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $username = $_POST['user_name'];
  $password = $_POST['password'];
  $selected_role = $_POST['role'];

  $sql = "SELECT * FROM users WHERE username=?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $username);
  $stmt->execute();

  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {

      // ✅ CHECK IF ROLE MATCHES
      if ($selected_role !== $user['role']) {
        echo "Access denied! Incorrect role selected.";
        exit();
      }

      // ✅ Correct login
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['role'] = $user['role'];

      if ($user['role'] == "Admin") {
        header("Location: ../admin/dashboard.php");
      } elseif ($user['role'] == "Doctor") {
        header("Location: ../doctor/dashboard.php");
      } else {
        header("Location: ../patient/dashboard.php");
      }
      exit();
    } else {
      echo "Invalid password!";
    }
  } else {
    echo "User not found!";
  }
}