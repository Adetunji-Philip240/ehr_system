<?php
require_once "../backend/db.php";


session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Admin') {
  header("Location: ../index.html");
  exit();
}

// Count doctors
$doctorCount = $conn->query("SELECT COUNT(*) as total FROM doctors")->fetch_assoc()['total'];

// Count patients
$patientCount = $conn->query("SELECT COUNT(*) as total FROM patients")->fetch_assoc()['total'];

// Count Records
$recordCount = $conn->query("SELECT COUNT(*) as total FROM records")->fetch_assoc()['total'];


// Count Pending Release Requests
$pendingRelease = $conn->query("
  SELECT COUNT(*) as total 
  FROM release_requests 
  WHERE status = 'Pending'
")->fetch_assoc()['total'];
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard</title>

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

  <!--Chart.js-->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    .card {
      background: #0f172a;
      /* dark navy */
      color: #fff;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    }

    canvas {
      margin-top: 15px;
    }
  </style>
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
        " href="#" class="w3-bar-item py-3"><i class="fas fa-chart-line mx-3 fs-4"></i>DASHBOARD</a>
    <a href="doctors.php" class="w3-bar-item py-3"><i class="fas fa-user-md mx-3 fs-4"></i>DOCTORS</a>
    <a href="patients.php" class="w3-bar-item py-3"><i class="fas fa-user mx-3 fs-4"></i>PATIENTS</a>
  </div>

  <!--Page Content-->
  <div class="page-content">
    <!--Top Bar-->
    <div class="top-bar d-flex justify-content-between p-4 fs-3">
      <li>Hello Admin</li>
      <li>
        <a href="../backend/logout.php"><i class="fas fa-sign-out-alt"></i></a>
      </li>
    </div>

    <!--Section 1-->
    <div class="mt-5 container">
      <div class="row">
        <div class="col-sm-3 mb-3">
          <div class="blockA text-center">
            <p class="fw-bold fs-6">Number of Doctors</p>
            <p class="fs-3"><?= $doctorCount ?></p>

            <a href="add_doctors.php"> <button class="btnA">Add Doctor</button></a>
          </div>
        </div>
        <div class="col-sm-3 mb-3">
          <div class="blockA text-center">
            <p class="fw-bold fs-6">Number of Patients</p>
            <p class="fs-3"><?= $patientCount ?></p>
            <a href="add_patients.php">
              <button class="btnA">Add Patient</button></a>
          </div>
        </div>

        <div class="col-sm-3 mb-3">
          <div class="blockA text-center">
            <p class="fw-bold fs-6">Number of Records</p>
            <p class="fs-3"><?= $recordCount ?></p>
            <a href="records_list.php">
              <button class="btnA">View Records</button></a>
          </div>
        </div>

        <div class="col-sm-3 mb-3">
          <div class="blockA text-center">
            <p class="fw-bold fs-6">Pending Release</p>
            <p class="fs-3" id="pending_release"><?= $pendingRelease ?></p>
            <a href="release_requests.php">
              <button class="btnA">View Requests</button></a>
          </div>
        </div>
      </div>

      <!--Charts Section-->

      <div class="container mt-5">
        <div class="row">

          <div class="col-md-6 mb-4">
            <div class="card p-3">
              <h5 class="text-center">Doctor vs Assignments</h5>
              <canvas id="doctorChart"></canvas>
            </div>
          </div>

          <div class="col-md-6 mb-4">
            <div class="card p-3">
              <h5 class="text-center">Patients vs Records</h5>
              <canvas id="patientChart"></canvas>
            </div>
          </div>

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


      <a href="doctors.php"><i class="fas fa-user-md mx-3 fs-4"></i> <br /><span
          style="font-size: 10px">DOCTORS</span></a>

      <a href="patients.php"><i class="fas fa-user mx-3 fs-4"></i> <br /><span
          style="font-size: 10px">PATIENTS</span></a>
    </div>
  </div>

  <!--New Request for release-->
  <script>
    function fetchPending() {
      fetch("../backend/get_pending.php")
        .then(response => response.text())
        .then(data => {
          document.getElementById("pending_release").innerText = data;
        })
        .catch(err => console.log(err));
    }

    // Run immediately
    fetchPending();

    // Auto refresh every 5 seconds
    setInterval(fetchPending, 5000);
  </script>

  <!--Bootstrap-->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


  <script>
    let doctorChart;
    let patientChart;

    function createGradient(ctx, color) {
      const gradient = ctx.createLinearGradient(0, 0, 0, 400);
      gradient.addColorStop(0, color);
      gradient.addColorStop(1, "rgba(42,142,241,0.2)");
      return gradient;
    }

    function loadCharts() {
      fetch("../backend/dashboard_data.php")
        .then(res => res.json())
        .then(data => {

          // Destroy old charts
          if (doctorChart) doctorChart.destroy();
          if (patientChart) patientChart.destroy();

          // ===== DOCTOR CHART =====
          const ctx1 = document.getElementById("doctorChart").getContext("2d");
          const gradient1 = createGradient(ctx1, "#2a8ef1");

          doctorChart = new Chart(ctx1, {
            type: 'bar',
            data: {
              labels: data.doctorLabels,
              datasets: [{
                label: 'Assignments',
                data: data.doctorData,
                backgroundColor: gradient1,
                borderRadius: 10, // 🔵 Rounded bars
                borderSkipped: false
              }]
            },
            options: {
              responsive: true,
              plugins: {
                legend: {
                  labels: {
                    color: "#fff" // 🌙 Dark mode text
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

          // ===== PATIENT CHART =====
          const ctx2 = document.getElementById("patientChart").getContext("2d");
          const gradient2 = createGradient(ctx2, "#2a8ef1");

          patientChart = new Chart(ctx2, {
            type: 'bar',
            data: {
              labels: data.patientLabels,
              datasets: [{
                label: 'Records',
                data: data.patientData,
                backgroundColor: gradient2,
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

    // Load + auto refresh
    loadCharts();
    setInterval(loadCharts, 5000);
  </script>

  <!--Custom JS-->
  <script src="../index.js"></script>
</body>

</html>