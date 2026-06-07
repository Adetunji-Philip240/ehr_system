<?php
session_start();
require_once "../backend/db.php";

$doctor_id = $_GET['id'];

// Get doctor info
$sql = "
SELECT d.*, u.username
FROM doctors d
JOIN users u ON d.user_id = u.id
WHERE d.id=?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();

// Count patients
$countPatients = $conn->query("SELECT COUNT(*) as total FROM assignments WHERE doctor_id=$doctor_id")->fetch_assoc()['total'];

// Count records
$countRecords = $conn->query("SELECT COUNT(*) as total FROM records WHERE doctor_id=$doctor_id")->fetch_assoc()['total'];
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Doctor Profile</title>

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

    <a href="doctors.php"><button class="btnA mt-2 mb-3 float-end">Back</button></a>
    <div class="row mt-5 block1">
      <div class="col-sm-6 mb-3 block2a">
        <p><b>Doctor Code:</b> <span><?= $doctor['doctor_code'] ?></span></p>
        <p><b>Name:</b> <span><?= $doctor['full_name'] ?></span></p>
        <p><b>Gender:</b> <span><?= $doctor['gender'] ?></span></p>
        <p><b>Phone Number:</b> <span><?= $doctor['phone'] ?></span></p>
        <p><b>Email:</b> <span><?= $doctor['email'] ?></span></p>
        <p><b>Number of Records:</b> <span><?= $countRecords ?></span></p>


      </div>
      <div class="col-sm-6 mb-3">
        <p><b>Specialty:</b> <span><?= $doctor['specialization'] ?></span></p>
        <p><b>Department:</b> <span><?= $doctor['department'] ?></span></p>
        <p><b>Number of Assignment:</b> <span><?= $countPatients ?></span></p>

        <p><b>Username:</b> <span><?= $doctor['username'] ?></span></p>
        <p><b>Password:</b> <span style="color:red;">••••••••</span> <button class="btn btn-warning btn-sm"
            data-bs-toggle="modal" data-bs-target="#resetModal">
            Reset Password
          </button></p>


        <p><b>Date Added:</b> <span><?= date("d M Y", strtotime($doctor['date_joined'])) ?></span></p>
      </div>
    </div>



    <div class="container mt-5">
      <form action="../backend/assign_patient.php" method="POST">
        <input type="hidden" name="doctor_id" value="<?= $doctor_id ?>">

        <select name="patient_id" class="form-control" required>
          <option value="">Select Patient</option>
          <?php
          // Only show unassigned patients
          $patients = $conn->query("
        SELECT * FROM patients 
        WHERE id NOT IN (
  SELECT patient_id FROM assignments WHERE status='Active'
)
    ");

          while ($p = $patients->fetch_assoc()) {
            echo "<option value='{$p['id']}'>{$p['full_name']}</option>";
          }
          ?>
        </select>

        <button class="btnA mt-2">Assign Patient</button>
      </form>

      <form id="assignmentForm" class="row mb-2 mt-2">
        <div class="col-md-6 mb-2">
          <input type="text" name="a_search" class="form-control" placeholder="Search patient name...">
        </div>

        <div class="col-md-6 mb-2">
          <select name="a_status" class="form-control">
            <option value="">All Status</option>
            <option value="Active">Active</option>
            <option value="Released">Released</option>
          </select>
        </div>

      </form>
      <div class="table-responsive-sm block3">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>S/N</th>
              <th>Patient ID</th>
              <th>Full Name</th>
              <th>Gender</th>
              <!-- <th>Records</th> -->

              <th>Status</th>
              <th>Assigned Date</th>
              <th>Released Date</th>
              <th>Action</th>
            </tr>
          </thead>

          <tbody id="assignmentsTable"></tbody>
        </table>
      </div>

      <div id="assignmentsPagination" class="mt-2"></div>


    </div>
  </div>

  <!--Bootstrap-->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <?php if (isset($_SESSION['password_reset'])): ?>
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999;">
      <div class="toast show text-bg-success">
        <div class="toast-header">
          <strong class="me-auto">Password Reset</strong>
        </div>
        <div class="toast-body">
          New Password: <b><?= $_SESSION['password_reset']['password'] ?></b>
        </div>
      </div>
    </div>

    <?php unset($_SESSION['password_reset']); ?>
  <?php endif; ?>



  <div class="modal fade" id="resetModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">

        <form action="../backend/reset_doctor_password.php" method="POST">

          <div class="modal-header">
            <h5 class="modal-title">Reset Doctor Password</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">

            <input type="hidden" name="doctor_id" value="<?= $doctor_id ?>">

            <label>Enter Admin Passcode</label>
            <input type="password" name="admin_code" class="form-control" required>

          </div>

          <div class="modal-footer">
            <button class="btn btn-danger">Reset Password</button>
          </div>

        </form>

      </div>
    </div>
  </div>



  <!--For Assignments-->
  <script>
    function loadAssignments(page = 1) {
      let search = document.querySelector("[name='a_search']").value;
      let status = document.querySelector("[name='a_status']").value;

      fetch(
          `../backend/fetch_doc_assignments.php?doctor_id=<?= $doctor_id ?>&page=${page}&search=${encodeURIComponent(search)}&status=${status}`
        )
        .then(res => res.json())
        .then(data => {
          document.getElementById("assignmentsTable").innerHTML = data.table;
          document.getElementById("assignmentsPagination").innerHTML = data.pagination;

          document.querySelectorAll(".a-page-btn").forEach(btn => {
            btn.addEventListener("click", () => {
              loadAssignments(btn.dataset.page);
            });
          });
        });
    }

    // prevent reload
    document.getElementById("assignmentForm").addEventListener("submit", function(e) {
      e.preventDefault();
      loadAssignments(1);
    });

    // live search
    document.querySelector("[name='a_search']").addEventListener("keyup", () => {
      loadAssignments(1);
    });

    // filter change
    document.querySelector("[name='a_status']").addEventListener("change", () => {
      loadAssignments(1);
    });

    // initial load
    loadAssignments();
  </script>

  <!--For Messages-->

  <!-- <script>
  function loadMessages(page = 1) {
    fetch(`../backend/fetch_messages.php?doctor_id=<?= $doctor_id ?>&page=${page}`)
      .then(res => res.json())
      .then(data => {
        document.getElementById("messagesTable").innerHTML = data.table;
        document.getElementById("messagesPagination").innerHTML = data.pagination;

        document.querySelectorAll(".m-page-btn").forEach(btn => {
          btn.addEventListener("click", () => {
            loadMessages(btn.dataset.page);
          });
        });
      });
  }

  loadMessages();
  </script> -->



  <!--Custom JS-->
  <script src="../index.js"></script>
</body>

</html>