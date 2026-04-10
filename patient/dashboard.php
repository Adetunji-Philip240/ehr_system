<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Patient Dashboard</title>

    <!--Creating a FavIcon-->
    <link rel="icon" type="image/x-icon" href="../images/logo.png" />
    <!--Bootstrap-->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />

    <!--W3 CSS-->
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css" />

    <!-- FONT AWESOME LINK -->
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    />

    <!--Custom CSS-->
    <link rel="stylesheet" href="../styles.css" />
  </head>
  <body>
    <!--Sidebar-->
    <div class="w3-sidebar w3-bar-block" style="width: 20%">
      <div class="w3-bar-item mt-5 mb-5">
        <img src="../images/logo.png" class="mx-auto d-block logo" alt="" />
      </div>
      <a
        style="
          background-color: var(--color3);
          color: var(--color2);
          border-radius: 0 10px 10px 0;
        "
        class="w3-bar-item py-3"
        ><i class="fas fa-chart-line mx-3 fs-4"></i>DASHBOARD</a
      >
      <a href="/" class="w3-bar-item py-3"
        ><i class="fas fa-user mx-3 fs-4"></i>PATIENTS</a
      >
      <a href="/" class="w3-bar-item py-3"
        ><i class="fas fa-clipboard mx-3 fs-4"></i>RECORDS</a
      >
    </div>

    <!--Page Content-->
    <div class="page-content">
      <!--Top Bar-->
      <div class="top-bar d-flex justify-content-between p-4 fs-3">
        <li>Hello Doctor <span id="doctor_name"></span></li>
        <li>
          <a href="admin-logout.php"><i class="fas fa-sign-out-alt"></i></a>
        </li>
      </div>

      <!--Section 1-->
      <div class="mt-5 container">
        <div class="row">
          <div class="col-sm-6">
            <div class="blockA text-center">
              <p class="fw-bold fs-6">My Patients</p>
              <p class="fs-3">0</p>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="blockA text-center">
              <p class="fw-bold fs-6">Number of Records</p>
              <p class="fs-3">0</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!--Bootstrap-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!--Custom JS-->
    <script src="../index.js"></script>
  </body>
</html>
