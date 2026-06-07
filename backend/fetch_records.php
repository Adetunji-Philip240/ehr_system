<?php
require_once "db.php";

$patient_id = (int)$_GET['id'];
$r_search = $_GET['r_search'] ?? '';
$r_page = (int)($_GET['r_page'] ?? 1);

$doctor_id = isset($_GET['doctor_id']) && $_GET['doctor_id'] !== ''
  ? (int)$_GET['doctor_id']
  : null;

$r_limit = 10;
$r_offset = ($r_page - 1) * $r_limit;

// BASE QUERY
$where = "WHERE patient_id=$patient_id";

// ADD DOCTOR FILTER ONLY IF EXISTS
if ($doctor_id) {
  $where .= " AND doctor_id=$doctor_id";
}

// SEARCH
if (!empty($r_search)) {
  $r_search = $conn->real_escape_string($r_search);
  $where .= " AND record_code LIKE '%$r_search%'";
}

// FETCH
$result = $conn->query("
    SELECT * FROM records
    $where
    ORDER BY created_at DESC
    LIMIT $r_limit OFFSET $r_offset
");

// COUNT
$count = $conn->query("
    SELECT COUNT(*) as total FROM records $where
")->fetch_assoc()['total'];

$totalPages = ceil($count / $r_limit);

// TABLE
$output = '';
$sn = $r_offset + 1;

while ($row = $result->fetch_assoc()) {
  $output .= "
        <tr>
            <td>{$sn}</td>
            <td>{$row['record_code']}</td>
            <td>" . date('d M Y', strtotime($row['created_at'])) . "</td>
            <td>
                <a href='../uploads/{$row['id']}.pdf' class='btn btn-primary btn-sm'>
                    Download
                </a>
            </td>
        </tr>
    ";
  $sn++;
}

// PAGINATION
$pagination = '';
for ($i = 1; $i <= $totalPages; $i++) {
  $pagination .= "<button class='page-btn btn btn-sm btn-outline-primary m-1' data-page='$i'>$i</button>";
}

echo json_encode([
  "table" => $output,
  "pagination" => $pagination
]);
