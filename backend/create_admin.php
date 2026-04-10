<?php
require_once "db.php";

$username = "admin";
$password = password_hash("admin123", PASSWORD_DEFAULT);
$role = "Admin";

$sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $username, $password, $role);

if ($stmt->execute()) {
  echo "Admin created successfully!";
} else {
  echo "Error: " . $conn->error;
}