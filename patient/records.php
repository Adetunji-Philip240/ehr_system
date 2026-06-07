<?php
session_start();
require_once "../backend/db.php";

// SECURITY CHECK
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Patient') {
  header("Location: ../index.html");
  exit();
}

$user_id = $_SESSION['user_id'];

// =========================
// GET PATIENT DETAILS
// =========================
$stmt = $conn->prepare("SELECT * FROM patients WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();

$patient_id = $patient['id'];

// =========================
// FILTERS
// =========================
$search = $_GET['search'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Pagination
$limit = 10;
$offset = ($page - 1) * $limit;

// =========================
// WHERE CONDITIONS
// =========================
$where = "WHERE r.patient_id = $patient_id";

if (!empty($search)) {
  $where .= " AND (
    r.record_code LIKE '%$search%' OR
    d.full_name LIKE '%$search%' OR
    d.doctor_code LIKE '%$search%'
  )";
}

if (!empty($start_date) && !empty($end_date)) {
  $where .= " AND DATE(r.created_at) BETWEEN '$start_date' AND '$end_date'";
}

// =========================
// MAIN QUERY (NOW SAFE)
// =========================
$records = $conn->query("
  SELECT 
    r.*,
    d.full_name AS doctor_name,
    d.doctor_code
  FROM records r
  JOIN doctors d ON r.doctor_id = d.id
  $where
  ORDER BY r.created_at DESC
  LIMIT $limit OFFSET $offset
");

// =========================
// COUNT QUERY
// =========================
$countQuery = $conn->query("
  SELECT COUNT(*) as total
  FROM records r
  JOIN doctors d ON r.doctor_id = d.id
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
  <title>Patient Records</title>

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
  <!--Sidebar-->
  <div class="w3-sidebar w3-bar-block" style="width: 20%">
    <div class="w3-bar-item mt-5 mb-5">
      <img src="../images/logo.png" class="mx-auto d-block logo" alt="" />
    </div>
    <a href="dashboard.php" class="w3-bar-item py-3"><i class="fas fa-chart-line mx-3 fs-4"></i>DASHBOARD</a>
    <a href="assignments.php" class="w3-bar-item py-3"><i class="fas fa-link mx-3 fs-4"></i>ASSIGNMENT</a>
    <a style="
          background-color: var(--color3);
          color: var(--color2);
          border-radius: 0 10px 10px 0;
        " class="w3-bar-item py-3"><i class="fas fa-clipboard mx-3 fs-4"></i>RECORDS</a>
  </div>

  <!--Page Content-->
  <div class="page-content">
    <!--Top Bar-->
    <div class="top-bar d-flex justify-content-between p-4 fs-3">
      <li>
        Hello <span><?= $patient['full_name'] ?></span> (<?=
                                                          $patient['patient_code'] ?>)
      </li>
      <li>
        <a href="../backend/logout.php"><i class="fas fa-sign-out-alt"></i></a>
      </li>
    </div>

    <div class="container mt-5">
      <h3>Your Records</h3>

      <form method="GET" class="row mb-3">

        <div class="col-md-4 mb-2">
          <input type="text" name="search" class="form-control" placeholder="Search record code or doctor..."
            value="<?= $search ?>">
        </div>

        <div class="col-md-3 mb-2">
          <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
        </div>

        <div class="col-md-3 mb-2">
          <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
        </div>

        <div class="col-md-2 mb-2">
          <button class="btn btn-primary w-100">Filter</button>
        </div>

      </form>

      <div class="table-responsive-sm">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>S/N</th>
              <th>Record Code</th>
              <th>Doctor</th>
              <th>Date Submitted</th>
              <th>Download</th>
            </tr>
          </thead>
          <?php if ($records->num_rows == 0): ?>
            <tr>
              <td colspan="5" class="text-center text-muted">
                No records available
              </td>
            </tr>
          <?php endif; ?>
          <tbody>
            <?php $sn = $offset + 1;  ?>
            <?php while ($row = $records->fetch_assoc()): ?>
              <tr>
                <td><?= $sn++ ?></td>

                <td><?= $row['record_code'] ?></td>

                <td>
                  <?= $row['doctor_name'] ?><br>
                  <small class="text-muted">(<?= $row['doctor_code'] ?>)</small>
                </td>

                <td><?= date("d M Y", strtotime($row['created_at'])) ?></td>

                <td>
                  <a href="../uploads/<?= $row['id'] ?>.pdf" class="btn btn-sm btn-primary" target="_blank">
                    Download
                  </a>
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
                href="?page=<?= $i ?>&search=<?= $search ?>&start_date=<?= $start_date ?>&end_date=<?= $end_date ?>">
                <?= $i ?>
              </a>
            </li>
          <?php endfor; ?>

        </ul>
      </nav>
    </div>
  </div>

  <div style="margin-top: 100px"></div>
  <!--Bottom Navbar-->

  <div id="bottom-navbar" class="navbar navbar-expand-sm fixed-bottom d-lg-none d-md-none">
    <div class="container-fluid d-flex justify-content-between">
      <a href="dashboard.php"> <i class=" fas fa-chart-line mx-3 fs-4"></i> <br /><span
          style="font-size: 10px">DASHBOARD</span></a>

      <a href="assignments.php"><i class=" fas fa-link mx-3 fs-4"></i> <br /><span
          style="font-size: 10px">ASSIGNMENTS</span></a>

      <a style="
            border: 1px solid var(--color2);
            color: var(--color2);
            padding: 5px;
            border-radius: 10px;"><i class="fas fa-clipboard mx-3 fs-4"></i> <br /><span
          style="font-size: 10px">RECORDS</span></a>
    </div>
  </div>
</body>

</html>