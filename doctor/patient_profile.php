<?php
session_start();
require_once "../backend/db.php";

// Security check
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Doctor') {
  header("Location: ../index.html");
  exit();
}

$user_id = $_SESSION['user_id'];
$patient_id = $_GET['id'];

// =========================
// GET DOCTOR
// =========================
$stmt = $conn->prepare("SELECT * FROM doctors WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();
$doctor_id = $doctor['id'];

// =========================
// GET PATIENT DETAILS
// =========================
$stmt = $conn->prepare("
  SELECT p.*, a.status, a.assigned_date, a.released_date
  FROM patients p
  JOIN assignments a ON p.id = a.patient_id
  WHERE p.id=? AND a.doctor_id=?
  ORDER BY a.assigned_date DESC
  LIMIT 1
");
$stmt->bind_param("ii", $patient_id, $doctor_id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();

// =========================
// RECORD FILTERS
// =========================
$r_search = $_GET['r_search'] ?? '';
$r_page = isset($_GET['r_page']) ? (int)$_GET['r_page'] : 1;

$r_limit = 10;
$r_offset = ($r_page - 1) * $r_limit;

// WHERE
$r_where = "WHERE patient_id=$patient_id AND doctor_id=$doctor_id";

if (!empty($r_search)) {
  $r_where .= " AND record_code LIKE '%$r_search%'";
}

// =========================
// FETCH RECORDS
// =========================
$records_list = $conn->query("
  SELECT * FROM records
  $r_where
  ORDER BY created_at DESC
  LIMIT $r_limit OFFSET $r_offset
");

// COUNT
$r_count = $conn->query("
  SELECT COUNT(*) as total FROM records
  $r_where
")->fetch_assoc()['total'];

$r_totalPages = ceil($r_count / $r_limit);

// =========================
// ASSIGNMENT FILTERS
// =========================
$a_status = $_GET['a_status'] ?? '';
$a_page = isset($_GET['a_page']) ? (int)$_GET['a_page'] : 1;

$a_limit = 10;
$a_offset = ($a_page - 1) * $a_limit;

// WHERE
$a_where = "WHERE patient_id=$patient_id AND doctor_id=$doctor_id";

if (!empty($a_status)) {
  $a_where .= " AND status='$a_status'";
}

// =========================
// FETCH ASSIGNMENTS
// =========================
$assignments = $conn->query("
  SELECT * FROM assignments
  $a_where
  ORDER BY assigned_date DESC
  LIMIT $a_limit OFFSET $a_offset
");

// COUNT
$a_count = $conn->query("
  SELECT COUNT(*) as total FROM assignments
  $a_where
")->fetch_assoc()['total'];

$a_totalPages = ceil($a_count / $a_limit);
?>


<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Patient Profile - Doctor</title>

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

    <?php if (isset($_SESSION['msg'])): ?>
      <div class="alert alert-info">
        <?= $_SESSION['msg']; ?>
      </div>
      <?php unset($_SESSION['msg']); ?>
    <?php endif; ?>

    <div class="block1 row mt-5 ">
      <div class="col-sm-6 mb-3">

        <div class="block2a">
          <p><b>Patient Code:</b> <span><?= $patient['patient_code'] ?></span></p>
          <p><b>Full Name:</b> <span><?= $patient['full_name'] ?></span></p>
          <p><b>DOB:</b> <span><?= date("d M Y", strtotime($patient['dob'])) ?></span></p>
          <p><b>Records:</b> <span><?= $r_count ?></span></p>
          <p><b>Status:</b>
            <?php if ($patient['status'] == 'Active'): ?>
              <span class="badge bg-success">Active</span>
            <?php else: ?>
              <span class="badge bg-secondary">Released</span>
            <?php endif; ?>
          </p>
        </div>
      </div>

      <div class="col-sm-6 mb-3">
        <p><b>Address:</b> <span><?= $patient['address'] ?></span></p>
        <p><b>Phone Number:</b> <span><?= $patient['phone'] ?></span></p>
        <p><b>Gender:</b> <span><?= $patient['gender'] ?></span></p>


        <p><b>Email:</b> <span><?= $patient['email'] ?></span></p>


        <form action="../backend/request_release.php" method="POST">
          <input type="hidden" name="patient_id" value="<?= $patient_id ?>">
          <button class="btnA">
            Request Release
          </button>
        </form>
      </div>
    </div>


  </div>

  <div class="container">
    <div class="row">
      <div class="col-sm-6 mb-3">
        <h4>List of Records</h4>

        <form id="recordSearchForm" class="row mb-2">
          <input type="hidden" name="id" value="<?= $patient_id ?>">
          <div class="mt-2">

            <input type="text" name="r_search" class="form-control" placeholder="Search record code..."
              value="<?= $r_search ?>">
          </div>

        </form>
        <div class="table-responsive-sm">
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

        <div id="recordsPagination"></div>

        <form method="GET" class="row mt-2 mb-2">
          <input type="hidden" name="id" value="<?= $patient_id ?>">
          <div class="mt-2">
            <select name="a_status" class="form-control">
              <option value="">All Status</option>
              <option value="Active" <?= $a_status == 'Active' ? 'selected' : '' ?>>Active</option>
              <option value="Released" <?= $a_status == 'Released' ? 'selected' : '' ?>>Released</option>
            </select>
          </div>

        </form>

        <h4>Assignments</h4>
        <div class="table-responsive-sm">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>S/N</th>
                <th>Status</th>
                <th>Assigned Date</th>
                <th>Released Date</th>
              </tr>
            </thead>


            <tbody id="assignmentsTable"></tbody>

          </table>
        </div>

        <div id="assignmentsPagination"></div>
      </div>
      <div class="col-sm-6 mb-3">
        <div class="block3">
          <h4 class="text-center">Patient Medical Record Entry Form</h4>

          <?php
          $isReleased = ($patient['status'] == 'Released');
          ?>
          <form action="../backend/save_record.php" method="POST">

            <input type="hidden" name="patient_id" value="<?= $patient_id ?>">
            <input type="hidden" name="doctor_id" value="<?= $doctor_id ?>">


            <?php if ($isReleased): ?>
              <div class="alert alert-warning text-center">
                Assignment released—medical record submission is closed.
              </div>
            <?php endif; ?>

            <div class="row">
              <div class="col-sm-6 mb-3">
                <div class="mt-2 mb-2">
                  <label for="reason_for_visit" class="form-label">Reason for Visit</label>
                  <input type="text" class="form-control" name="reason_for_visit"
                    placeholder="e.g. Routine checkup, Follow-up visit, Emergency visit" required />
                </div>

                <div class="mb-2">
                  <label for="chief_complaint" class="form-label">Chief Complaint</label>
                  <input type="text" name="chief_complaint" class="form-control"
                    placeholder="e.g. Severe headache, Chest pain, Abdominal pain" />
                </div>

                <div class="mb-2">
                  <label for="symptoms" class="form-label">Symptoms</label>
                  <textarea name="symptoms" class="form-control" rows="3"
                    placeholder="e.g. Fever, headache, nausea, fatigue"></textarea>
                </div>

                <div class="mb-2">
                  <label for="vital_signs" class="form-label">Vital Signs</label>
                  <textarea name="vital_signs" class="form-control" rows="3"
                    placeholder="e.g. Temperature: 38°C, BP: 120/80 mmHg, Pulse: 80 bpm"></textarea>
                </div>
              </div>
              <div class="col-sm-6 mb-3">
                <div class="mt-2 mb-2">
                  <label for="diagnosis" class="form-label">Diagnosis</label>
                  <textarea name="diagnosis" class="form-control" rows="3"
                    placeholder="e.g. Malaria, Typhoid fever, Hypertension"></textarea>
                </div>

                <div class="mb-2">
                  <label for="treatment" class="form-label">Treatment</label>
                  <textarea name="treatment" class="form-control" rows="3"
                    placeholder="e.g. Antimalarial therapy, IV fluids, Bed rest"></textarea>
                </div>

                <div class="mb-2">
                  <label for="prescription" class="form-label">Prescription</label>
                  <textarea name="prescription" class="form-control" rows="3"
                    placeholder="e.g. Paracetamol 500mg twice daily, Coartem for 3 days"></textarea>
                </div>

                <div class="mb-2">
                  <label for="doctor_notes" class="form-label">Doctor Notes</label>
                  <textarea name="doctor_notes" class="form-control" rows="3"
                    placeholder="Additional notes... e.g. Patient should return in 3 days for review"></textarea>
                </div>
              </div>
            </div>

            <button type="submit" class="btnA mt-2 d-block mx-auto" <?= $isReleased ? 'disabled' : '' ?>>
              Submit Record
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!--For Records-->
  <script>
    function loadRecords(page = 1) {
      let search = document.querySelector("[name='r_search']").value;

      fetch(
          `../backend/fetch_records.php?id=<?= $patient_id ?>&doctor_id=<?= $doctor_id ?>&r_page=${page}&r_search=${search}`
        )
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


  <!--For Assignment-->

  <script>
    function loadAssignments(page = 1) {
      let status = document.querySelector("[name='a_status']").value;

      fetch(
          `../backend/fetch_assignments.php?id=<?= $patient_id ?>&doctor_id=<?= $doctor_id ?>&a_page=${page}&a_status=${status}`
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

    // FILTER (no reload)
    document.querySelector("[name='a_status']").addEventListener("change", () => {
      loadAssignments(1);
    });

    // INITIAL LOAD
    loadAssignments();
  </script>

  <!--Bootstrap-->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!--Custom JS-->
  <script src="../index.js"></script>
</body>

</html>