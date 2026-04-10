<?php
session_start();
require_once "../backend/db.php";

// Security check
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Doctor') {
  header("Location: ../index.html");
  exit();
}

$user_id = $_SESSION['user_id'];

// =========================
// GET DOCTOR DETAILS
// =========================
$stmt = $conn->prepare("SELECT * FROM doctors WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();

$doctor_id = $doctor['id'];
$doctor_name = $doctor['full_name'];

// =========================
// GET PATIENTS (ACTIVE + RELEASED)
// =========================
$sql = "
SELECT 
  p.*, 
  a.status, 
  a.assigned_date, 
  a.released_date
FROM assignments a
JOIN patients p ON a.patient_id = p.id
WHERE a.doctor_id = ?
ORDER BY a.assigned_date DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
?>


<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Patients - Doctor</title>

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
    <a style="
          background-color: var(--color3);
          color: var(--color2);
          border-radius: 0 10px 10px 0;
        " class="w3-bar-item py-3"><i class="fas fa-user mx-3 fs-4"></i>PATIENTS</a>
    <a href="/" class="w3-bar-item py-3"><i class="fas fa-clipboard mx-3 fs-4"></i>RECORDS</a>
  </div>

  <!--Page Content-->
  <div class="page-content">
    <!--Top Bar-->
    <div class="top-bar d-flex justify-content-between p-4 fs-3">
      <li>
        Hello Dr. <?= $doctor_name ?> (<?= $doctor['doctor_code'] ?>)
      </li>
      <li>
        <a href="admin-logout.php"><i class="fas fa-sign-out-alt"></i></a>
      </li>
    </div>

    <!--Section 1-->
    <div class="mt-5 container">
      <h3>Your Patient(s)</h3>

      <div class="row mb-3">
        <div class="col-md-4">
          <input type="text" id="searchInput" class="form-control" placeholder="Search patient...">
        </div>

        <div class="col-md-3">
          <select id="statusFilter" class="form-control">
            <option value="">All Status</option>
            <option value="Active">Active</option>
            <option value="Released">Released</option>
          </select>
        </div>
      </div>
      <div class="table-responsive-sm">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>S/N</th>
              <th>Patient ID</th>
              <th>Full Name</th>
              <th>DOB</th>
              <th>Address</th>
              <th>Phone</th>
              <th>Email</th>
              <th>Records</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="patientTable">
            <?php
            $sn = 1;
            while ($row = $result->fetch_assoc()) {
            ?>
              <tr>
                <td><?= $sn++ ?></td>
                <td><?= $row['patient_code'] ?></td>
                <td><?= $row['full_name'] ?></td>
                <td><?= date("d M Y", strtotime($row['dob'])) ?></td>
                <td><?= $row['address'] ?></td>
                <td><?= $row['phone'] ?></td>
                <td><?= $row['email'] ?></td>

                <!-- RECORD COUNT -->
                <td>
                  <?=
                  $conn->query("
        SELECT COUNT(*) as total 
        FROM records 
        WHERE patient_id={$row['id']} AND doctor_id=$doctor_id
      ")->fetch_assoc()['total'];
                  ?>
                </td>

                <!-- STATUS -->
                <td>
                  <?php if ($row['status'] == 'Active'): ?>
                    <span class="badge bg-success">Active</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Released</span>
                  <?php endif; ?>
                </td>

                <!-- ACTION -->
                <td>
                  <a href="patient_profile.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">
                    View
                  </a>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
      <div id="paginationContainer"></div>
    </div>
  </div>


  <script>
    const rowsPerPage = 5;
    let currentPage = 1;

    const tableRows = Array.from(document.querySelectorAll("#patientTable tr"));

    // =========================
    // FILTER FUNCTION
    // =========================
    function filterRows() {
      let search = document.getElementById("searchInput").value.toLowerCase();
      let status = document.getElementById("statusFilter").value.toLowerCase();

      return tableRows.filter(row => {
        let text = row.innerText.toLowerCase();
        let stat = row.cells[8].innerText.toLowerCase(); // Status column

        return (
          text.includes(search) &&
          (status === "" || stat.includes(status))
        );
      });
    }

    // =========================
    // DISPLAY TABLE
    // =========================
    function displayTable() {
      let filtered = filterRows();

      tableRows.forEach(row => row.style.display = "none");

      let start = (currentPage - 1) * rowsPerPage;
      let end = start + rowsPerPage;

      filtered.slice(start, end).forEach(row => {
        row.style.display = "";
      });

      setupPagination(filtered.length);
    }

    // =========================
    // PAGINATION
    // =========================
    function setupPagination(totalRows) {
      const pagination = document.createElement("ul");
      pagination.className = "pagination mt-3";

      let pageCount = Math.ceil(totalRows / rowsPerPage);

      for (let i = 1; i <= pageCount; i++) {
        let li = document.createElement("li");
        li.className = "page-item " + (i === currentPage ? "active" : "");

        let btn = document.createElement("button");
        btn.className = "page-link";
        btn.innerText = i;

        btn.addEventListener("click", () => {
          currentPage = i;
          displayTable();
        });

        li.appendChild(btn);
        pagination.appendChild(li);
      }

      let container = document.getElementById("paginationContainer");
      container.innerHTML = "";
      container.appendChild(pagination);
    }

    // =========================
    // EVENTS
    // =========================
    document.getElementById("searchInput").addEventListener("keyup", () => {
      currentPage = 1;
      displayTable();
    });

    document.getElementById("statusFilter").addEventListener("change", () => {
      currentPage = 1;
      displayTable();
    });

    // =========================
    // INIT
    // =========================
    displayTable();
  </script>

  <!--Bootstrap-->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!--Custom JS-->
  <script src="../index.js"></script>
</body>

</html>