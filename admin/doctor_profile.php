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
  </style>
</head>

<body>
  <div class="container-fluid p-5">
    <img src="../images/logo.png" class="mx-auto d-block logo" alt="" />

    <a href="doctors.php"><button class="btnA mt-2 float-end">Back</button></a>
    <div class="row mt-5">
      <div class="col-sm-6 mb-3">
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



    <div class="container">
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

      <div class="table-responsive-sm">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>S/N</th>
              <th>Patient ID</th>
              <th>Full Name</th>
              <th>Gender</th>
              <th>Records</th>

              <th>Status</th>
              <th>Assigned Date</th>
              <th>Released Date</th>
              <th>View</th>
            </tr>
          </thead>

          <tbody>
            <?php
            $sql = "
SELECT p.*, a.status, a.assigned_date, a.released_date,
(SELECT COUNT(*) FROM records WHERE patient_id=p.id) as total_records
FROM assignments a
JOIN patients p ON a.patient_id = p.id
WHERE a.doctor_id = $doctor_id
ORDER BY a.assigned_date DESC
";

            $result = $conn->query($sql);
            $sn = 1;

            while ($row = $result->fetch_assoc()) {
            ?>
            <tr>
              <td><?= $sn++ ?></td>
              <td><?= $row['patient_code'] ?></td>
              <td><?= $row['full_name'] ?></td>
              <td><?= $row['gender'] ?></td>
              <td><?= $row['total_records'] ?></td>

              <td>
                <?php if ($row['status'] == 'Active') { ?>
                <span class="badge bg-success">Active</span>
                <?php } else { ?>
                <span class="badge bg-secondary">Released</span>
                <?php } ?>
              </td>

              <td><?= date("d M Y", strtotime($row['assigned_date'])) ?></td>

              <td>
                <?= $row['released_date']
                    ? date("d M Y", strtotime($row['released_date']))
                    : '-' ?>
              </td>
              <td>
                <a href="../backend/remove_assignment.php?doctor_id=<?= $doctor_id ?>&patient_id=<?= $row['id'] ?>"
                  class="btn btn-danger btn-sm" onclick="return confirm('Release this patient from doctor?');">
                  Release
                </a>
                <a href="patient_profile.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">
                  View
                </a>
              </td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        <div class="row">
          <div class="col-sm-6 mb-3">
            <h3>Messages</h3>
            <div class="table-responsive-sm">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>S/N</th>
                    <th>Message</th>
                    <th>Date (message)</th>
                    <th>Reply</th>
                    <th>Date (reply)</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
          <div class="col-sm-6 mb-3">
            <p>Send Message to Doctor <span><?= $doctor['full_name'] ?></span></p>

            <form action="../backend/send_message.php" method="POST">
              <input type="hidden" name="doctor_id" value="<?= $doctor_id ?>">
              <textarea name="message" class="form-control" required></textarea>
              <button class="btnA mt-2">Send</button>
            </form>

            <div class="table-responsive-sm">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>S/N</th>
                    <th>Message</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $sql = "SELECT * FROM messages WHERE doctor_id=? ORDER BY created_at DESC";
                  $stmt = $conn->prepare($sql);
                  $stmt->bind_param("i", $doctor_id);
                  $stmt->execute();
                  $result = $stmt->get_result();

                  $sn = 1;

                  while ($row = $result->fetch_assoc()) {
                  ?>
                  <tr>
                    <td><?= $sn++ ?></td>
                    <td><?= htmlspecialchars($row['message']) ?></td>
                    <td><?= date("d M Y H:i", strtotime($row['created_at'])) ?></td>
                  </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
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




  <!--Custom JS-->
  <script src="../index.js"></script>
</body>

</html>