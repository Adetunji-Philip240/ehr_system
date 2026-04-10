<?php
session_start();
$doctor = $_SESSION['doctor_created'] ?? null;
unset($_SESSION['doctor_created']);
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Add Doctors</title>

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
    <a href="dashboard.php" class="w3-bar-item py-3"><i class="fas fa-chart-line mx-3 fs-4"></i>DASHBOARD</a>
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
      <h3>Add Doctors</h3>

      <form action="../backend/add_doctor.php" method="POST">
        <div class="row">
          <div class="col-sm-6">
            <div class="mt-3 mb-3">
              <label for="fullName" class="form-label">Full Name:</label>
              <input type="text" class="form-control" name="full_name" required />
            </div>
          </div>
          <div class="col-sm-6">
            <div class="mt-3 mb-3">
              <label for="specialty" class="form-label">Specialty:</label>
              <input type="text" class="form-control" name="specialty" required />
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-6">
            <div class="mt-3 mb-3">
              <label for="department" class="form-label">Department:</label>
              <input type="text" class="form-control" name="department" required />
            </div>
          </div>
          <div class="col-sm-6">
            <div class="mt-3 mb-3">
              <label for="phoneNumber" class="form-label">Phone Number:</label>
              <input type="tel" class="form-control" name="phone_number" required />
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-sm-6">
            <div class="mt-3 mb-3">
              <label for="email" class="form-label">Email:</label>
              <input type="email" class="form-control" name="email" required />
            </div>
          </div>
          <div class="col-sm-6">
            <div class="mt-3 mb-3">
              <label for="gender" class="form-label">Gender:</label>
              <br />
              <input type="radio" value="Male" name="gender" required /><label class="mx-2" for="male">Male</label>
              <br />
              <input type="radio" value="Female" name="gender" required /><label class="mx-2"
                for="female">Female</label>
            </div>
          </div>
        </div>

        <button class="btnA">Add Doctor</button>
      </form>
    </div>
  </div>

  <!-- Toast -->
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="doctorToast" class="toast align-items-center text-bg-success border-0" role="alert">
      <div class="d-flex">
        <div class="toast-body">
          <strong>Doctor Created Successfully 🎉</strong>
          <br />
          Username: <span id="toastUsername"></span><br />
          Password: <span id="toastPassword"></span>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  </div>

  <!--Bootstrap-->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <?php if ($doctor): ?>
  <script>
  document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("toastUsername").innerText = "<?= $doctor['username'] ?>";
    document.getElementById("toastPassword").innerText = "<?= $doctor['password'] ?>";

    let toast = new bootstrap.Toast(document.getElementById('doctorToast'));
    toast.show();
  });
  </script>
  <?php endif; ?>

  <!--Custom JS-->
  <script src="../index.js"></script>
</body>

</html>