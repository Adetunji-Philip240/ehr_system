<?php
session_start();
require_once "../backend/db.php";

// Pagination settings
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filters
$search = $_GET['search'] ?? '';
$doctor = $_GET['doctor'] ?? '';
$patient = $_GET['patient'] ?? '';

// Build WHERE clause
$where = "WHERE 1=1";

if (!empty($search)) {
  $where .= " AND (r.record_code LIKE '%$search%')";
}

if (!empty($doctor)) {
  $where .= " AND d.full_name LIKE '%$doctor%'";
}

if (!empty($patient)) {
  $where .= " AND p.full_name LIKE '%$patient%'";
}

// Total count (for pagination)
$totalQuery = "
SELECT COUNT(*) as total
FROM records r
JOIN patients p ON r.patient_id = p.id
JOIN doctors d ON r.doctor_id = d.id
$where
";

$total = $conn->query($totalQuery)->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);

// Main query
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
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Records List</title>

  <link rel="icon" type="image/x-icon" href="../images/logo.png" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
  <link rel="stylesheet" href="../styles.css" />

  <style>
  table {
    font-size: 12px;
  }

  .block3 {
    border: 1px solid var(--color3);
    border-radius: 20px;
    padding: 15px;
    box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
  }
  </style>
</head>

<body>

  <div class="container-fluid p-5">
    <img src="../images/logo.png" class="mx-auto d-block logo" alt="" />

    <a href="dashboard.php">
      <button class="btnA mt-2 mb-3 float-end">Back</button>
    </a>

    <div class="container">

      <h3>List of Records</h3>

      <!-- SEARCH + FILTER -->
      <form onsubmit="return false;" class="row mb-3">
        <div class="col-md-4 mb-2">
          <input type="text" name="search" class="form-control" placeholder="Search Record Code"
            value="<?= htmlspecialchars($search) ?>">
        </div>

        <div class="col-md-4 mb-2">
          <input type="text" name="patient" class="form-control" placeholder="Filter by Patient"
            value="<?= htmlspecialchars($patient) ?>">
        </div>

        <div class="col-md-4 mb-2">
          <input type="text" name="doctor" class="form-control" placeholder="Filter by Doctor"
            value="<?= htmlspecialchars($doctor) ?>">
        </div>


      </form>

      <div class="table-responsive-sm block3">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>S/N</th>
              <th>Record Code</th>
              <th>Patient Name</th>
              <th>Doctor Name</th>
              <th>Date</th>
              <th>View</th>
            </tr>
          </thead>

          <tbody id="recordsTable"></tbody>
        </table>
      </div>
    </div>

    <ul class="pagination justify-content-center mt-2" id="pagination"></ul>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  function fetchRecords(page = 1) {
    let search = document.querySelector("[name=search]").value;
    let doctor = document.querySelector("[name=doctor]").value;
    let patient = document.querySelector("[name=patient]").value;

    fetch(`../backend/fetch_records_admin.php?search=${search}&doctor=${doctor}&patient=${patient}&page=${page}`)
      .then(res => res.json())
      .then(data => {
        document.getElementById("recordsTable").innerHTML = data.table;
        document.getElementById("pagination").innerHTML = data.pagination;
      });
  }

  // 🔥 LIVE SEARCH (typing)
  document.querySelectorAll("input").forEach(input => {
    input.addEventListener("keyup", () => {
      fetchRecords(1);
    });
  });

  // 🔥 PAGINATION CLICK
  document.addEventListener("click", function(e) {
    if (e.target.classList.contains("page-btn")) {
      e.preventDefault();
      let page = e.target.getAttribute("data-page");
      fetchRecords(page);
    }
  });

  // Initial load
  fetchRecords();
  </script>


  <script src="../index.js"></script>

</body>

</html>