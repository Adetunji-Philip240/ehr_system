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
// COUNT ASSIGNMENTS
// =========================
$assignments = $conn->query("
  SELECT COUNT(*) as total 
  FROM assignments 
  WHERE patient_id = $patient_id
")->fetch_assoc()['total'];

// =========================
// COUNT RECORDS
// =========================
$records = $conn->query("
  SELECT COUNT(*) as total 
  FROM records 
  WHERE patient_id = $patient_id
")->fetch_assoc()['total'];
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Patient Dashboard</title>

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
</head>

<body>
  <!--Sidebar-->
  <div class="w3-sidebar w3-bar-block" style="width: 20%">
    <div class="w3-bar-item mt-5 mb-5">
      <img src="../images/logo.png" class="mx-auto d-block logo" alt="" />
    </div>
    <a style="
          background-color: var(--color3);
          color: var(--color2);
          border-radius: 0 10px 10px 0;
        " class="w3-bar-item py-3"><i class="fas fa-chart-line mx-3 fs-4"></i>DASHBOARD</a>
    <a href="assignments.php" class="w3-bar-item py-3"><i class="fas fa-link mx-3 fs-4"></i>ASSIGNMENTS</a>
    <a href="records.php" class="w3-bar-item py-3"><i class="fas fa-clipboard mx-3 fs-4"></i>RECORDS</a>
  </div>

  <!--Page Content-->
  <div class="page-content">
    <!--Top Bar-->
    <div class="top-bar d-flex justify-content-between p-4 fs-3">
      <li>Hello <span><?= $patient['full_name'] ?></span>
        (<?= $patient['patient_code'] ?>) </li>
      <li>
        <a href="../backend/logout.php"><i class="fas fa-sign-out-alt"></i></a>
      </li>
    </div>

    <!--Section 1-->
    <div class="mt-5 container">
      <div class="row">
        <div class="col-sm-6">
          <div class="blockA text-center">
            <p class="fw-bold fs-6">Number of Assignments</p>
            <p class="fs-3" id="totalAssignments"><?= $assignments ?></p>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="blockA text-center">
            <p class="fw-bold fs-6">Number of Records</p>
            <p class="fs-3" id="totalRecords"><?= $records ?></p>
          </div>
        </div>
      </div>


      <!--Chart Section-->
      <div class="container mt-5">
        <div class="card p-4 bg-dark text-white">
          <h5 class="text-center">Assignments vs Records</h5>
          <canvas id="patientChart"></canvas>
        </div>
      </div>
    </div>
  </div>


  <div style="margin-top: 100px"></div>
  <!--Bottom Navbar-->

  <div id="bottom-navbar" class="navbar navbar-expand-sm fixed-bottom d-lg-none d-md-none">
    <div class="container-fluid d-flex justify-content-between">
      <a style="
            border: 1px solid var(--color2);
            color: var(--color2);
            padding: 5px;
            border-radius: 10px;"> <i class=" fas fa-chart-line mx-3 fs-4"></i> <br /><span
          style="font-size: 10px">DASHBOARD</span></a>

      <a href="assignments.php"><i class=" fas fa-link mx-3 fs-4"></i> <br /><span
          style="font-size: 10px">ASSIGNMENTS</span></a>

      <a href="records.php"><i class="fas fa-clipboard mx-3 fs-4"></i> <br /><span
          style="font-size: 10px">RECORDS</span></a>
    </div>
  </div>



  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <script>
    let patientChart;

    function loadPatientChart() {
      fetch("../backend/patient_chart_data.php")
        .then(res => res.json())
        .then(data => {

          // =====================
          // UPDATE TEXT BLOCKS
          // =====================
          document.getElementById("totalAssignments").innerText = data.assignments;
          document.getElementById("totalRecords").innerText = data.records;

          // =====================
          // DESTROY OLD CHART
          // =====================
          if (patientChart) patientChart.destroy();

          const ctx = document.getElementById("patientChart").getContext("2d");

          const gradient = ctx.createLinearGradient(0, 0, 0, 400);
          gradient.addColorStop(0, "#2a8ef1");
          gradient.addColorStop(1, "rgba(42,142,241,0.2)");

          patientChart = new Chart(ctx, {
            type: "bar",
            data: {
              labels: ["Assignments", "Records"],
              datasets: [{
                label: "Total",
                data: [data.assignments, data.records],
                backgroundColor: [gradient, gradient],
                borderRadius: 10,
                borderSkipped: false
              }]
            },
            options: {
              responsive: true,
              plugins: {
                legend: {
                  labels: {
                    color: "#fff"
                  }
                }
              },
              scales: {
                x: {
                  ticks: {
                    color: "#ccc"
                  },
                  grid: {
                    color: "rgba(255,255,255,0.1)"
                  }
                },
                y: {
                  beginAtZero: true,
                  ticks: {
                    color: "#ccc"
                  },
                  grid: {
                    color: "rgba(255,255,255,0.1)"
                  }
                }
              }
            }
          });

        });
    }

    // INIT
    loadPatientChart();

    // AUTO REFRESH EVERY 5 SECONDS
    setInterval(loadPatientChart, 5000);
  </script>

  <!--Bootstrap-->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!--Custom JS-->
  <script src="../index.js"></script>
</body>

</html>