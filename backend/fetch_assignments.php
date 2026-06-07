<?php
require_once "db.php";

header('Content-Type: application/json');

$patient_id = $_GET['id'];
$doctor_id = $_GET['doctor_id'];
$a_status = $_GET['a_status'] ?? '';
$a_page = $_GET['a_page'] ?? 1;

$a_limit = 10;
$a_offset = ($a_page - 1) * $a_limit;

$where = "WHERE patient_id=$patient_id AND doctor_id=$doctor_id";

if (!empty($a_status)) {
  $where .= " AND status='$a_status'";
}

// FETCH
$result = $conn->query("
  SELECT * FROM assignments
  $where
  ORDER BY assigned_date DESC
  LIMIT $a_limit OFFSET $a_offset
");

// COUNT
$count = $conn->query("SELECT COUNT(*) as total FROM assignments $where")
  ->fetch_assoc()['total'];

$totalPages = ceil($count / $a_limit);

// TABLE
$output = '';
$sn = $a_offset + 1;

while ($row = $result->fetch_assoc()) {
  $statusBadge = $row['status'] == 'Active'
    ? "<span class='badge bg-success'>Active</span>"
    : "<span class='badge bg-secondary'>Released</span>";

  $released = $row['released_date']
    ? date("d M Y", strtotime($row['released_date']))
    : '—';

  $output .= "
    <tr>
      <td>{$sn}</td>
      <td>{$statusBadge}</td>
      <td>" . date("d M Y", strtotime($row['assigned_date'])) . "</td>
      <td>{$released}</td>
    </tr>
  ";

  $sn++;
}

// EMPTY STATE
if ($output == '') {
  $output = "<tr><td colspan='4' class='text-center'>No assignments found</td></tr>";
}

// PAGINATION
$pagination = '';
for ($i = 1; $i <= $totalPages; $i++) {
  $pagination .= "<button class='a-page-btn btn btn-sm btn-outline-primary m-1' data-page='$i'>$i</button>";
}

echo json_encode([
  "table" => $output,
  "pagination" => $pagination
]);