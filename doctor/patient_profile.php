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
// COUNT RECORDS
// =========================
$records = $conn->query("
  SELECT COUNT(*) as total 
  FROM records 
  WHERE patient_id=$patient_id AND doctor_id=$doctor_id
")->fetch_assoc()['total'];

// =========================
// GET RECORD LIST
// =========================
$records_list = $conn->query("
  SELECT * FROM records 
  WHERE patient_id=$patient_id AND doctor_id=$doctor_id
");

// =========================
// GET ASSIGNMENT HISTORY
// =========================
$assignments = $conn->query("
  SELECT * FROM assignments 
  WHERE patient_id=$patient_id AND doctor_id=$doctor_id
  ORDER BY assigned_date DESC
");
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
  </style>
</head>

<body>
  <div class="container-fluid p-5">
    <img src="../images/logo.png" class="mx-auto d-block logo" alt="" />
    <a href="patients.php"><button class="btnA mt-2 float-end">Back</button></a>

    <?php if (isset($_SESSION['msg'])): ?>
    <div class="alert alert-info">
      <?= $_SESSION['msg']; ?>
    </div>
    <?php unset($_SESSION['msg']); ?>
    <?php endif; ?>

    <div class="row mt-5">
      <div class="col-sm-6 mb-3">
        <p><b>Patient Code:</b> <span><?= $patient['patient_code'] ?></span></p>
        <p><b>Full Name:</b> <span><?= $patient['full_name'] ?></span></p>
        <p><b>DOB:</b> <span><?= date("d M Y", strtotime($patient['dob'])) ?></span></p>
        <p><b>Records:</b> <span><?= $records ?></span></p>
        <p><b>Status:</b>
          <?php if ($patient['status'] == 'Active'): ?>
          <span class="badge bg-success">Active</span>
          <?php else: ?>
          <span class="badge bg-secondary">Released</span>
          <?php endif; ?>
        </p>
      </div>

      <div class="col-sm-6 mb-3">
        <p><b>Address:</b> <span><?= $patient['address'] ?></span></p>
        <p><b>Phone Number:</b> <span><?= $patient['phone'] ?></span></p>
        <p><b>Email:</b> <span><?= $patient['email'] ?></span></p>

        <p><b>Phone Number:</b> <span><?= $patient['phone'] ?></span></p>
        <p><b>Email:</b> <span><?= $patient['email'] ?></span></p>
      </div>
    </div>

    <form action="../backend/request_release.php" method="POST">
      <input type="hidden" name="patient_id" value="<?= $patient_id ?>">
      <button class="btnA mt-2 d-block mx-auto">
        Request Release
      </button>
    </form>
  </div>

  <div class="container">
    <div class="row">
      <div class="col-sm-6 mb-3">
        <h4>List of Records</h4>
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

            <tbody>
              <?php $sn = 1;
              while ($row = $records_list->fetch_assoc()) { ?>
              <tr>
                <td><?= $sn++ ?></td>
                <td><?= $row['id'] ?></td>
                <td><?= date("d M Y", strtotime($row['created_at'])) ?></td>
                <td>
                  <a href="../uploads/<?= $row['id'] ?>.pdf" target="_blank">
                    View
                  </a>
                </td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>

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

            <tbody>
              <?php $sn = 1;
              while ($row = $assignments->fetch_assoc()) { ?>
              <tr>
                <td><?= $sn++ ?></td>

                <td>
                  <?php if ($row['status'] == 'Active'): ?>
                  <span class="badge bg-success">Active</span>
                  <?php else: ?>
                  <span class="badge bg-secondary">Released</span>
                  <?php endif; ?>
                </td>

                <td><?= date("d M Y", strtotime($row['assigned_date'])) ?></td>

                <td>
                  <?= $row['released_date']
                      ? date("d M Y", strtotime($row['released_date']))
                      : '—' ?>
                </td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="col-sm-6 mb-3">
        <h4 class="text-center">Patient Medical Record Entry Form</h4>
        <form action="">
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

          <button type="submit" class="btnA mt-2 d-block mx-auto">
            Submit Record
          </button>
        </form>
      </div>
    </div>
  </div>
  <!--Bootstrap-->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!--Custom JS-->
  <script src="../index.js"></script>
</body>

</html>