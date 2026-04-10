<?php
require_once "db.php";

$result = $conn->query("
  SELECT COUNT(*) as total 
  FROM release_requests 
  WHERE status='Pending'
");

echo $result->fetch_assoc()['total'];