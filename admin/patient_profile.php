  <?php
  session_start();
  require_once "../backend/db.php";

  $patient_id = $_GET['id'];
  $r_search = '';

  // Patient details
  $sql = "
  SELECT p.*, u.username, u.password
  FROM patients p
  JOIN users u ON p.user_id = u.id
  WHERE p.id = ?
  ";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $patient_id);
  $stmt->execute();
  $patient = $stmt->get_result()->fetch_assoc();
  // Count records
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

      .block1 {

        width: 95%;
        margin: auto;
        border: 1px solid var(--color3);
        border-radius: 20px;
        padding: 15px;
        box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
      }

      .block2a {
        border-right: 3px dotted var(--color3);
      }

      .block3 {
        border: 1px solid var(--color3);
        border-radius: 20px;
        padding: 15px;
        box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
      }

      /*For Mobile Screen*/
      @media (max-width: 767px) {
        .block2a {
          border-right: none;
        }
      }
    </style>
  </head>

  <body>
    <div class="container-fluid p-5">
      <img src="../images/logo.png" class="mx-auto d-block logo" alt="" />
      <a href="patients.php"><button class="btnA mt-2 mb-3 float-end">Back</button></a>
      <div class="row mt-5 block1">
        <div class="col-sm-6 mb-3 block2a">
          <p><b>Patient Code:</b> <span><?= $patient['patient_code'] ?></span></p>
          <p><b>Name:</b> <span><?= $patient['full_name'] ?></span></p>
          <p><b>Gender:</b> <span><?= $patient['gender'] ?></span></p>
          <p><b>DOB:</b> <span><?= date("d M Y", strtotime($patient['dob'])) ?></span></p>
          <p><b>Records:</b> <span><?= $records ?></span></p>
        </div>
        <div class="col-sm-6 mb-3">
          <p><b>Address:</b> <span><?= $patient['address'] ?></span></p>
          <p><b>Phone Number:</b> <span><?= $patient['phone'] ?></span></p>
          <p><b>Email:</b> <span><?= $patient['email'] ?></span></p>


          <p><b>Username:</b> <span><?= $patient['username'] ?></span></p>

          <p><b>Password:</b>
            <span style="color:red;">••••••••</span>
            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#resetModal">
              Reset Password
            </button>
          </p>
        </div>
      </div>

      <div class="container mt-5">


        <h4>Doctor(s) Assigned To</h4>
        <div class="row">
          <div class="col-sm-6 mb-2">
            <input type="text" id="doctorSearch" class="form-control" placeholder="Search doctor...">
          </div>
          <div class="col-sm-6 mb-2">
            <select id="doctorStatus" class="form-control ">
              <option value="">All Status</option>
              <option value="Active">Active</option>
              <option value="Released">Released</option>
            </select>
          </div>
        </div>
        <div class="table-responsive-sm block3">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>S/N</th>
                <th>Doctor ID</th>
                <th>Full Name</th>
                <th>Records</th>
                <th>Assigned Date</th>
                <th>Released Date</th>
              </tr>
            </thead>
            <tbody id="doctorTable"></tbody>
          </table>
        </div>
        <div id="doctorPagination" class="mt-2"></div>
      </div>

      <div class="container mt-2 mx-auto w-75">

        <h4>List of Records</h4>

        <form id="recordSearchForm" class="row mb-2">
          <input type="hidden" name="id" value="<?= $patient_id ?>">
          <div class="mt-2">

            <input type="text" name="r_search" class="form-control" placeholder="Search record code...">
          </div>

        </form>
        <div class="table-responsive-sm block3">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>S/N</th>
                <th>Record Code</th>
                <th>Date of Submit</th>
                <th>View Record</th>
              </tr>
            </thead>


            <tbody id="recordsTable"></tbody>

          </table>
        </div>

        <div id="recordsPagination" class="mt-2"></div>
      </div>



    </div>
    <!--Bootstrap-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <?php if (isset($_SESSION['patient_reset'])): ?>
      <div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999;">
        <div class="toast show text-bg-success">
          <div class="toast-header">
            <strong>Password Reset 🎉</strong>
          </div>
          <div class="toast-body">
            New Password: <b><?= $_SESSION['patient_reset']['password'] ?></b>
          </div>
        </div>
      </div>

      <?php unset($_SESSION['patient_reset']); ?>
    <?php endif; ?>

    <div class="modal fade" id="resetModal">
      <div class="modal-dialog">
        <div class="modal-content">

          <div class="modal-header">
            <h5>Reset Patient Password</h5>
          </div>

          <form action="../backend/reset_patient_password.php" method="POST">
            <div class="modal-body">

              <input type="hidden" name="patient_id" value="<?= $patient_id ?>">

              <label>Enter Admin Passcode</label>
              <input type="password" name="admin_code" class="form-control" required>

            </div>

            <div class="modal-footer">
              <button class="btn btn-success">Reset</button>
            </div>

          </form>

        </div>
      </div>
    </div>



    <!--Assignments-->
    <script>
      function loadDoctors(page = 1) {

        let search = document.getElementById("doctorSearch").value;
        let status = document.getElementById("doctorStatus").value;

        fetch(`../backend/fetch_patient_doctors.php?
    patient_id=<?= $patient_id ?>&
    page=${page}&
    search=${search}&
    status=${status}
  `)
          .then(res => res.json())
          .then(data => {
            document.getElementById("doctorTable").innerHTML = data.table;
            document.getElementById("doctorPagination").innerHTML = data.pagination;

            document.querySelectorAll(".doctor-page").forEach(btn => {
              btn.addEventListener("click", () => {
                loadDoctors(btn.dataset.page);
              });
            });
          });
      }

      // SEARCH (no reload)
      document.getElementById("doctorSearch").addEventListener("keyup", () => {
        loadDoctors(1);
      });

      // FILTER
      document.getElementById("doctorStatus").addEventListener("change", () => {
        loadDoctors(1);
      });

      loadDoctors();
    </script>


    <!--For Records-->
    <script>
      function loadRecords(page = 1) {
        let search = document.querySelector("input[name='r_search']").value || '';

        fetch(`../backend/fetch_records.php?id=<?= $patient_id ?>&r_page=${page}&r_search=${search}`)
          .then(res => res.json())
          .then(data => {
            document.getElementById("recordsTable").innerHTML = data.table;
            document.getElementById("recordsPagination").innerHTML = data.pagination;

            // attach click events
            document.querySelectorAll(".page-btn").forEach(btn => {
              btn.addEventListener("click", () => {
                loadRecords(btn.dataset.page);
              });
            });
          });
      }

      // PREVENT FORM SUBMIT (no reload)
      document.getElementById("recordSearchForm").addEventListener("submit", function(e) {
        e.preventDefault(); // 🚨 stops page reload
        loadRecords(1);
      });

      // SEARCH (no reload)
      document.querySelector("[name='r_search']").addEventListener("keyup", () => {
        loadRecords(1);
      });

      // INITIAL LOAD
      loadRecords();
    </script>
    <!--Custom JS-->
    <script src="../index.js"></script>
  </body>

  </html>