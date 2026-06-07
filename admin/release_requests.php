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

    <div style="position: relative; display: inline-block;">
      <i class="fas fa-bell fs-4"></i>

      <span id="notifCount" style="
      position: absolute;
      top: 0px;
      left: 28px; /* adjust based on your bell size */
      background: red;
      color: white;
      border-radius: 50%;
      padding: 2px 6px;
      font-size: 10px;
      line-height: 1;
    ">
        0
      </span>
    </div>


    <img src="../images/logo.png" class="mx-auto d-block logo" alt="" />
    <a href="dashboard.php"><button class="btnA mt-2 float-end">Back</button></a></table>

    <form method="GET" class="row mb-3">
      <div class="row mt-3 mb-3">

        <div class="col-md-3 mb-2">
          <input type="text" id="search" class="form-control" placeholder="Search...">
        </div>

        <div class="col-md-3 mb-2">
          <select id="status" class="form-control">
            <option value="">All Status</option>
            <option value="Pending">Pending</option>
            <option value="Approved">Approved</option>
            <option value="Rejected">Rejected</option>
          </select>
        </div>

        <div class="col-md-3 mb-2">
          <select id="range" class="form-control">
            <option value="">All Time</option>
            <option value="today">Today</option>
            <option value="week">This Week</option>
            <option value="month">This Month</option>
          </select>
        </div>

        <div class="col-md-3 mb-2">
          <button onclick="exportPDF()" class="btn btn-success w-100">
            Export PDF
          </button>
        </div>

      </div>
    </form>
    <div class="table-responsive-sm">
      <table class="table">
        <thead>
          <tr>
            <th>S/N</th>
            <th>Patient</th>
            <th>Doctor</th>
            <th>Request Date</th>
            <th>Status</th>
            <th>Action Date</th>
            <th>Action</th>
          </tr>
        </thead>

        <tbody id="tableBody">
          <?php $sn = 1; ?>
          <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $sn++ ?></td>

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

            <td><?= $row['updated_at'] ? $row['updated_at'] : '-' ?></td>

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

    <div id="paginationContainer" class="mt-3"></div>
  </div>


  <script>
  let currentPage = 1;

  function fetchData(page = 1) {
    currentPage = page;

    let search = document.getElementById("search").value;
    let status = document.getElementById("status").value;
    let range = document.getElementById("range").value;

    fetch(`../backend/fetch_release_requests.php?page=${page}&search=${search}&status=${status}&range=${range}`)
      .then(res => res.json())
      .then(res => {

        let tbody = document.getElementById("tableBody");
        tbody.innerHTML = "";

        res.data.forEach((row, index) => {
          tbody.innerHTML += `
          <tr>
           <td>${(currentPage - 1) * 7 + index + 1}</td>
            <td>${row.full_name}<br><small>(${row.patient_code})</small></td>
            <td>${row.doctor_name}<br><small>(${row.doctor_code})</small></td>
            <td>${row.created_at}</td>
            <td>
              <span class="badge ${getStatusColor(row.status)}">
                ${row.status}
              </span>
            </td>
            <td>${row.updated_at ?? '-'}</td>
            <td>
              ${row.status === 'Pending' ? `
                <a href="../backend/approve_release.php?id=${row.id}" class="btn btn-success btn-sm">Approve</a>
                <a href="../backend/reject_release.php?id=${row.id}" class="btn btn-danger btn-sm">Reject</a>
              ` : 'Completed'}
            </td>
          </tr>
        `;
        });

        setupPagination(res.totalPages);
        updateNotification();
      });
  }

  // STATUS COLOR
  function getStatusColor(status) {
    if (status === "Pending") return "bg-warning";
    if (status === "Approved") return "bg-success";
    return "bg-danger";
  }

  // PAGINATION
  function setupPagination(totalPages) {
    let container = document.getElementById("paginationContainer");
    container.innerHTML = "";

    for (let i = 1; i <= totalPages; i++) {
      container.innerHTML += `
      <button 
        class="btn btn-sm ${i === currentPage ? 'btn-primary' : 'btn-outline-primary'} m-1"
        onclick="fetchData(${i})">
        ${i}
      </button>
    `;
    }
  }

  // NOTIFICATION COUNT
  function updateNotification() {
    fetch("../backend/fetch_notification.php")
      .then(res => res.json())
      .then(res => {
        document.getElementById("notifCount").innerText = res.notifCount ?? 0;
      });
  }

  // EVENTS (LIVE)
  document.getElementById("search").addEventListener("keyup", () => fetchData(1));
  document.getElementById("status").addEventListener("change", () => fetchData(1));
  document.getElementById("range").addEventListener("change", () => fetchData(1));

  // AUTO REFRESH (REAL-TIME FEEL)
  setInterval(() => {
    updateNotification();
    fetchData(currentPage);
  }, 5000);

  // INITIAL LOAD
  fetchData();
  </script>


  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

  <script>
  async function exportPDF() {
    const {
      jsPDF
    } = window.jspdf;

    // Landscape mode
    const doc = new jsPDF("landscape");

    let rows = document.querySelectorAll("#tableBody tr");
    let data = [];

    rows.forEach((row, i) => {
      let cols = row.querySelectorAll("td");

      data.push([
        i + 1,
        cols[1].innerText,
        cols[2].innerText,
        cols[3].innerText,
        cols[4].innerText,
        cols[5].innerText
      ]);
    });

    // =========================
    // 🏥 ADD LOGO (TOP LEFT)
    // =========================
    const logo = new Image();
    logo.src = "../images/logo.png"; // your logo path

    logo.onload = function() {

      doc.addImage(logo, "PNG", 10, 5, 30, 30);

      // =========================
      // 🏥 HEADER TEXT
      // =========================
      doc.setFontSize(16);
      doc.text("Healix Hospital", 50, 15);

      doc.setFontSize(10);
      doc.text("Release Request Report", 50, 22);

      let today = new Date().toLocaleString();
      doc.text("Generated on: " + today, 50, 28);

      // =========================
      // 💧 WATERMARK (DIM LOGO)
      // =========================
      doc.setGState(new doc.GState({
        opacity: 0.08
      }));

      doc.addImage(logo, "PNG", 100, 60, 100, 100);

      // Reset opacity
      doc.setGState(new doc.GState({
        opacity: 1
      }));

      // =========================
      // 📊 TABLE
      // =========================
      doc.autoTable({
        startY: 40,
        head: [
          ["S/N", "Patient", "Doctor", "Request Date", "Status", "Action Date"]
        ],
        body: data,
        styles: {
          fontSize: 9
        },
        headStyles: {
          fillColor: [22, 160, 133]
        },

        didDrawPage: function(data) {
          // =========================
          // 🔢 PAGE NUMBER
          // =========================
          let pageCount = doc.internal.getNumberOfPages();
          doc.setFontSize(10);
          doc.text(
            "Page " + doc.internal.getCurrentPageInfo().pageNumber + " of " + pageCount,
            data.settings.margin.left,
            doc.internal.pageSize.height - 10
          );
        }
      });

      // =========================
      // ✍️ SIGNATURE SECTION
      // =========================
      let finalY = doc.lastAutoTable.finalY + 20;

      doc.setFontSize(10);
      doc.text("__________________________", 200, finalY);
      doc.text("Admin Signature", 210, finalY + 5);

      // =========================
      // 💾 SAVE
      // =========================
      doc.save("Release_Requests.pdf");
    };
  }
  </script>

  <!--Bootstrap-->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!--Custom JS-->
  <script src="../index.js"></script>

</body>

</html>