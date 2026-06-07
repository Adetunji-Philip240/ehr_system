<?php
require_once "db.php";

$doctor_id = $_GET['doctor_id'];
$page = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

$limit = 10;
$offset = ($page - 1) * $limit;

$where = "WHERE a.doctor_id=$doctor_id";

if (!empty($search)) {
  $where .= " AND p.full_name LIKE '%$search%'";
}

if (!empty($status)) {
  $where .= " AND a.status='$status'";
}

// FETCH
$result = $conn->query("
SELECT p.*, a.status, a.assigned_date, a.released_date
FROM assignments a
JOIN patients p ON a.patient_id = p.id
$where
ORDER BY a.assigned_date DESC
LIMIT $limit OFFSET $offset
");

// COUNT
$count = $conn->query("
SELECT COUNT(*) as total
FROM assignments a
JOIN patients p ON a.patient_id = p.id
$where
")->fetch_assoc()['total'];

$totalPages = ceil($count / $limit);

// TABLE
$output = '';
$sn = $offset + 1;

while ($row = $result->fetch_assoc()) {
  $statusBadge = $row['status'] == 'Active'
    ? "<span class='badge bg-success'>Active</span>"
    : "<span class='badge bg-secondary'>Released</span>";

  $released = $row['released_date']
    ? date("d M Y", strtotime($row['released_date']))
    : '-';

  $output .= "
    <tr>
      <td>{$sn}</td>
      <td>{$row['patient_code']}</td>
      <td>{$row['full_name']}</td>
      <td>{$row['gender']}</td>
      <td>{$statusBadge}</td>
      <td>" . date("d M Y", strtotime($row['assigned_date'])) . "</td>
      <td>{$released}</td>
      <td>
        <a href='../backend/remove_assignment.php?doctor_id={$doctor_id}&patient_id={$row['id']}' class='btn btn-danger btn-sm'>Release</a>
        <a href='patient_profile.php?id={$row['id']}' class='btn btn-primary btn-sm'>View</a>
      </td>
    </tr>
  ";
  $sn++;
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
