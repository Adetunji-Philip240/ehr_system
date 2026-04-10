<?php
session_start();
require_once "../backend/db.php";

// =========================
// FILTER VALUES
// =========================
$status = $_GET['status'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

// Pagination
$limit = 7;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// =========================
// WHERE CONDITIONS
// =========================
$where = "WHERE 1=1";

if (!empty($status)) {
  $where .= " AND rr.status = '$status'";
}

if (!empty($start_date) && !empty($end_date)) {
  $where .= " AND DATE(rr.created_at) BETWEEN '$start_date' AND '$end_date'";
}

// =========================
// MAIN QUERY
// =========================
$sql = "
SELECT 
  rr.*, 
  p.full_name, 
  p.patient_code,
  d.full_name AS doctor_name,
  d.doctor_code
FROM release_requests rr
JOIN patients p ON rr.patient_id = p.id
JOIN doctors d ON rr.doctor_id = d.id
$where
ORDER BY rr.created_at DESC
LIMIT $limit OFFSET $offset
";

$result = $conn->query($sql);

// =========================
// COUNT FOR PAGINATION
// =========================
$countQuery = $conn->query("
SELECT COUNT(*) as total
FROM release_requests rr
JOIN patients p ON rr.patient_id = p.id
JOIN doctors d ON rr.doctor_id = d.id
$where
");

$totalRows = $countQuery->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Patient Profile</title>

  <!--Creating a FavIcon-->
  <link rel="icon" type="image/x-icon" href="../images/logo.png" />
  <!--Bootstrap-->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!--W3 CSS-->
  <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css" />

  <!-- FONT AWESOME LINK -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />

  <!--Custom CSS-->
  <link rel="stylesheet" href="../styles.css" />
  <style>
  table {
    font-size: 12px;
  }
  </style>
</head>

<body>

  <div class="container-fluid p-5">

    <h3 class="text-center fs-3">Release Requests</h3>


    <img src="../images/logo.png" class="mx-auto d-block logo" alt="" />
    <a href="dashboard.php"><button class="btnA mt-2 float-end">Back</button></a></table>

    <form method="GET" class="row mb-3">
      <div class="row mt-3">
        <div class="col-md-3 mb-2">
          <select name="status" class="form-control">
            <option value="">All Status</option>
            <option value="Pending" <?= ($status == 'Pending') ? 'selected' : '' ?>>Pending</option>
            <option value="Approved" <?= ($status == 'Approved') ? 'selected' : '' ?>>Approved</option>
            <option value="Rejected" <?= ($status == 'Rejected') ? 'selected' : '' ?>>Rejected</option>
          </select>
        </div>

        <div class="col-md-3 mb-2">
          <input type="date" name="start_date" value="<?= $start_date ?>" class="form-control">
        </div>

        <div class="col-md-3 mb-2">
          <input type="date" name="end_date" value="<?= $end_date ?>" class="form-control">
        </div>

        <div class="col-md-3 mb-2">
          <button class="btn btn-outline-primary w-75">Filter</button>
        </div>
      </div>
    </form>
    <div class="table-responsive-sm">
      <table class="table">
        <thead>
          <tr>
            <th>Patient</th>
            <th>Doctor</th>
            <th>Request Date</th>
            <th>Status</th>
            <th>Action Date</th>
            <th>Action</th>
          </tr>
        </thead>

        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td>
              <?= $row['full_name'] ?>
              <br>
              <small class="text-muted">(<?= $row['patient_code'] ?>)</small>
            </td>

            <td>
              <?= $row['doctor_name'] ?>
              <br>
              <small class="text-muted">(<?= $row['doctor_code'] ?>)</small>
            </td>
            <td><?= $row['created_at'] ?></td>

            <td>
              <?php if ($row['status'] == 'Pending'): ?>
              <span class="badge bg-warning">Pending</span>
              <?php elseif ($row['status'] == 'Approved'): ?>
              <span class="badge bg-success">Approved</span>
              <?php else: ?>
              <span class="badge bg-danger">Rejected</span>
              <?php endif; ?>
            </td>

            <td>
              <?= $row['updated_at'] ? $row['updated_at'] : '-' ?>
            </td>

            <td>
              <?php if ($row['status'] == 'Pending'): ?>
              <a href="../backend/approve_release.php?id=<?= $row['id'] ?>" class="btn btn-success btn-sm">Approve</a>
              <a href="../backend/reject_release.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm">Reject</a>
              <?php else: ?>
              <span class="text-muted">Completed</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endwhile; ?>

        </tbody>

      </table>
    </div>

    <nav>
      <ul class="pagination">

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
          <a class="page-link"
            href="?page=<?= $i ?>&status=<?= $status ?>&start_date=<?= $start_date ?>&end_date=<?= $end_date ?>">
            <?= $i ?>
          </a>
        </li>
        <?php endfor; ?>

      </ul>
    </nav>
  </div>

  <!--Bootstrap-->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!--Custom JS-->
  <script src="../index.js"></script>

</body>

</html>