<?php
require_once "db.php";

$patient_id = $_GET['patient_id'];

// pagination
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// filters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

// =======================
// WHERE BUILDER
// =======================
$where = "WHERE a.patient_id = $patient_id";

if (!empty($search)) {
  $search = $conn->real_escape_string($search);
  $where .= " AND (d.full_name LIKE '%$search%' OR d.doctor_code LIKE '%$search%')";
}

if (!empty($status)) {
  $status = $conn->real_escape_string($status);
  $where .= " AND a.status = '$status'";
}

// =======================
// MAIN QUERY
// =======================
$sql = "
SELECT d.*, a.assigned_date, a.released_date, a.status, a.doctor_id
FROM assignments a
JOIN doctors d ON a.doctor_id = d.id
$where
ORDER BY a.assigned_date DESC
LIMIT $limit OFFSET $offset
";

$result = $conn->query($sql);

// =======================
// COUNT QUERY
// =======================
$countSql = "
SELECT COUNT(*) as total
FROM assignments a
JOIN doctors d ON a.doctor_id = d.id
$where
";

$count = $conn->query($countSql)->fetch_assoc()['total'];
$totalPages = ceil($count / $limit);

// =======================
// BUILD TABLE
// =======================
$table = "";
$sn = $offset + 1;

while ($row = $result->fetch_assoc()) {

  // record count per doctor-patient
  $recordCount = $conn->query("
    SELECT COUNT(*) as total
    FROM records
    WHERE doctor_id = {$row['doctor_id']}
    AND patient_id = $patient_id
  ")->fetch_assoc()['total'];

  $table .= "
    <tr>
      <td>{$sn}</td>
      <td>{$row['doctor_code']}</td>
      <td>{$row['full_name']}</td>
      <td>{$recordCount}</td>
      <td>" . date('d M Y', strtotime($row['assigned_date'])) . "</td>
      <td>" . ($row['released_date']
    ? date('d M Y', strtotime($row['released_date']))
    : '<span class=\"text-success\">Active</span>') . "</td>
    </tr>
  ";

  $sn++;
}

// =======================
// PAGINATION
// =======================
$pagination = "";

for ($i = 1; $i <= $totalPages; $i++) {
  $active = ($i == $page) ? "active" : "";

  $pagination .= "
    <button class='doctor-page btn btn-sm btn-primary m-1 $active' data-page='$i'>
      $i
    </button>
  ";
}

// =======================
// RESPONSE
// =======================
echo json_encode([
  "table" => $table,
  "pagination" => $pagination
]);
