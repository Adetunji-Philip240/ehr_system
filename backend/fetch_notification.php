<?php
require_once "db.php";

$notifQuery = $conn->query("
SELECT COUNT(*) as total
FROM release_requests
WHERE status = 'Pending'
");

$notifCount = $notifQuery->fetch_assoc()['total'];

echo json_encode([
  "notifCount" => $notifCount
]);