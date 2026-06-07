<?php
require_once "db.php";

// Get params
$search  = $_GET['search'] ?? '';
$doctor  = $_GET['doctor'] ?? '';
$patient = $_GET['patient'] ?? '';
$page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$limit = 10;
$offset = ($page - 1) * $limit;

// WHERE clause
$where = "WHERE 1=1";

if (!empty($search)) {
  $where .= " AND r.record_code LIKE '%$search%'";
}

if (!empty($doctor)) {
  $where .= " AND d.full_name LIKE '%$doctor%'";
}

if (!empty($patient)) {
  $where .= " AND p.full_name LIKE '%$patient%'";
}

// Total count
$totalQuery = "
SELECT COUNT(*) as total
FROM records r
JOIN patients p ON r.patient_id = p.id
JOIN doctors d ON r.doctor_id = d.id
$where
";

$total = $conn->query($totalQuery)->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);

// Fetch records
$sql = "
SELECT r.*, 
       p.full_name AS patient_name, p.patient_code,
       d.full_name AS doctor_name, d.doctor_code
FROM records r
JOIN patients p ON r.patient_id = p.id
JOIN doctors d ON r.doctor_id = d.id
$where
ORDER BY r.created_at DESC
LIMIT $limit OFFSET $offset
";

$result = $conn->query($sql);

// Build HTML
$output = "";

$sn = $offset + 1;

while ($row = $result->fetch_assoc()) {
  $output .= "
  <tr>
    <td>{$sn}</td>
    <td>{$row['record_code']}</td>
    <td>
      {$row['patient_name']}<br>
      <small>{$row['patient_code']}</small>
    </td>
    <td>
      {$row['doctor_name']}<br>
      <small>{$row['doctor_code']}</small>
    </td>
    <td>" . date("d M Y", strtotime($row['created_at'])) . "</td>
    <td>
      <a href='../uploads/{$row['id']}.pdf' class='btn btn-sm btn-primary' download>Download</a>
    </td>
  </tr>
  ";
  $sn++;
}

// Pagination HTML
$pagination = "";

for ($i = 1; $i <= $total_pages; $i++) {
  $active = ($i == $page) ? "active" : "";
  $pagination .= "<li class='page-item $active'>
    <a href='#' class='page-link page-btn' data-page='$i'>$i</a>
  </li>";
}

// Return JSON
echo json_encode([
  "table" => $output,
  "pagination" => $pagination
]);