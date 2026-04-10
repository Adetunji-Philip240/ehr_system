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
        <a href="admin-logout.php"><i class="fas fa-sign-out-alt"></i></a>
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
            <a href="add_patients.php">
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

  <!--Custom JS-->
  <script src="../index.js"></script>
</body>

</html>