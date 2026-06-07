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
$sql = "SELECT * FROM doctors WHERE user_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();

$doctor_id = $doctor['id'];
$doctor_name = $doctor['full_name'];

// =========================
// COUNT ASSIGNED PATIENTS
// =========================
$patients = $conn->query("
  SELECT COUNT(DISTINCT patient_id) as total 
  FROM assignments 
  WHERE doctor_id = $doctor_id AND status='Active'
")->fetch_assoc()['total'];

// =========================
// COUNT RECORDS
// =========================
$records = $conn->query("
  SELECT COUNT(*) as total 
  FROM records 
  WHERE doctor_id = $doctor_id
")->fetch_assoc()['total'];
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Doctor Dashboard</title>

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
    <a href="patients.php" class="w3-bar-item py-3"><i class="fas fa-user mx-3 fs-4"></i>PATIENTS</a>

  </div>

  <!--Page Content-->
  <div class="page-content">
    <!--Top Bar-->
    <div class="top-bar d-flex justify-content-between p-4 fs-3">
      <li>
        Hello Dr. <?= $doctor_name ?> (<?= $doctor['doctor_code'] ?>)
      </li>
      <li>
        <a href="../backend/logout.php"><i class="fas fa-sign-out-alt"></i></a>
      </li>
    </div>

    <!--Section 1-->
    <div class="mt-5 container">
      <div class="row">
        <div class="col-sm-6">
          <div class="blockA text-center">
            <p class="fw-bold fs-6">Active Patients</p>
            <p class="fs-3" id="activePatients"><?= $patients ?></p>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="blockA text-center">
            <p class="fw-bold fs-6">Number of Records</p>
            <p class="fs-3" id="totalRecords"><?= $records ?></p>
          </div>
        </div>
      </div>


      <!--Charts Section-->
      <div class="container mt-5">
        <div class="card p-4 bg-dark text-white">
          <h5 class="text-center">Active Patients Vs Number of Records</h5>
          <canvas id="doctorChart"></canvas>
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

      <a href="patients.php"><i class="fas fa-user mx-3 fs-4"></i> <br /><span
          style="font-size: 10px">PATIENTS</span></a>
    </div>
  </div>

  <!--Chart.js-->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <script>
    let doctorChart;

    function loadCharts() {
      fetch("../backend/doctor_chart_data.php")
        .then(res => res.json())
        .then(data => {

          // =========================
          // UPDATE TEXT BLOCKS
          // =========================
          document.getElementById("activePatients").innerText = data.patients;
          document.getElementById("totalRecords").innerText = data.records;

          // =========================
          // DESTROY OLD CHART
          // =========================
          if (doctorChart) doctorChart.destroy();

          const ctx = document.getElementById("doctorChart").getContext("2d");

          const gradient = ctx.createLinearGradient(0, 0, 0, 400);
          gradient.addColorStop(0, "#2a8ef1");
          gradient.addColorStop(1, "rgba(42,142,241,0.2)");

          doctorChart = new Chart(ctx, {
            type: "bar",
            data: {
              labels: ["Active Patients", "Number of Records"],
              datasets: [{
                label: "Total",
                data: [data.patients, data.records],
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

    // initial load
    loadCharts();

    // auto refresh every 5 seconds
    setInterval(loadCharts, 5000);
  </script>

  <!--Bootstrap-->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!--Custom JS-->
  <script src="../index.js"></script>
</body>

</html>