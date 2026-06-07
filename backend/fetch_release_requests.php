<?php
require_once "db.php";

$status = $_GET['status'] ?? '';
$range = $_GET['range'] ?? '';
$search = $_GET['search'] ?? '';
$page = $_GET['page'] ?? 1;

$limit = 7;
$offset = ($page - 1) * $limit;

$where = "WHERE 1=1";

// STATUS FILTER
if (!empty($status)) {
  $where .= " AND rr.status = '$status'";
}

// DATE FILTER
if ($range == "today") {
  $where .= " AND DATE(rr.created_at) = CURDATE()";
} elseif ($range == "week") {
  $where .= " AND YEARWEEK(rr.created_at, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($range == "month") {
  $where .= " AND MONTH(rr.created_at) = MONTH(CURDATE())";
}

// SEARCH
if (!empty($search)) {
  $where .= " AND (
    p.full_name LIKE '%$search%' 
    OR p.patient_code LIKE '%$search%' 
    OR d.full_name LIKE '%$search%' 
    OR d.doctor_code LIKE '%$search%'
  )";
}

// MAIN QUERY
$sql = "
SELECT rr.*, 
       p.full_name, p.patient_code,
       d.full_name AS doctor_name, d.doctor_code
FROM release_requests rr
JOIN patients p ON rr.patient_id = p.id
JOIN doctors d ON rr.doctor_id = d.id
$where
ORDER BY rr.created_at DESC
LIMIT $limit OFFSET $offset
";

$result = $conn->query($sql);

// COUNT
$countQuery = $conn->query("
SELECT COUNT(*) as total
FROM release_requests rr
JOIN patients p ON rr.patient_id = p.id
JOIN doctors d ON rr.doctor_id = d.id
$where
");

$totalRows = $countQuery->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// DATA
$data = [];

while ($row = $result->fetch_assoc()) {
  $data[] = $row;
}

echo json_encode([
  "data" => $data,
  "totalPages" => $totalPages
]);