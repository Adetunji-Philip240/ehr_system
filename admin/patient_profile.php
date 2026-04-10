  <?php
  session_start();
  require_once "../backend/db.php";

  $patient_id = $_GET['id'];

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
  $records = $conn->query("SELECT COUNT(*) as total FROM records WHERE patient_id=$patient_id")->fetch_assoc()['total'];
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
    </style>
  </head>

  <body>
    <div class="container-fluid p-5">
      <img src="../images/logo.png" class="mx-auto d-block logo" alt="" />
      <a href="patients.php"><button class="btnA mt-2 float-end">Back</button></a>
      <div class="row mt-5">
        <div class="col-sm-6 mb-3">
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

      <div class="container mt-2">
        <h4>Doctor(s) Assigned To</h4>

        <div class="table-responsive-sm">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>S/N</th>
                <th>Doctor ID</th>
                <th>Full Name</th>
                <th>Number of Records</th>
                <th>Assigned Date</th>
                <th>Released Date</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $sql = "
  SELECT d.*, a.assigned_date, a.released_date, a.status
  FROM assignments a
  JOIN doctors d ON a.doctor_id = d.id
  WHERE a.patient_id = $patient_id
  ";

              $result = $conn->query($sql);
              $sn = 1;

              while ($row = $result->fetch_assoc()) {
              ?>
                <tr>
                  <td><?= $sn++ ?></td>
                  <td><?= $row['doctor_code'] ?></td>
                  <td><?= $row['full_name'] ?></td>
                  <td>
                    <?= $conn->query("SELECT COUNT(*) as total FROM records WHERE doctor_id={$row['id']} AND patient_id=$patient_id")->fetch_assoc()['total']; ?>
                  </td>
                  <td><?= date("d M Y", strtotime($row['assigned_date'])) ?></td>

                  <td>
                    <?= $row['released_date']
                      ? date("d M Y", strtotime($row['released_date']))
                      : '<span class="text-success">Active</span>' ?>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="container mt-2">
        <div class="row">
          <div class="col-sm-6 mb-3">
            <h4>Records</h4>
            <div class="table-responsive-sm">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>S/N</th>
                    <th>Doctor ID</th>
                    <th>Download Record</th>
                    <th>Date Submitted</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $sql = "SELECT * FROM records WHERE patient_id=$patient_id";
                  $result = $conn->query($sql);
                  $sn = 1;

                  while ($row = $result->fetch_assoc()) {
                  ?>
                    <tr>
                      <td><?= $sn++ ?></td>
                      <td><?= $row['doctor_id'] ?></td>
                      <td>
                        <a href="../uploads/<?= $row['id'] ?>.pdf" target="_blank">
                          Download
                        </a>
                      </td>
                      <td><?= date("d M Y", strtotime($row['created_at'] ?? date("Y-m-d"))) ?></td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>

          <div class="col-sm-6 mb-3">
            <p>Send Message to <span id="full_name"></span></p>
            <form action="../backend/send_patient_message.php" method="POST">
              <input type="hidden" name="patient_id" value="<?= $patient_id ?>">
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
                  $sql = "SELECT * FROM patient_messages WHERE patient_id=? ORDER BY created_at DESC";
                  $stmt = $conn->prepare($sql);
                  $stmt->bind_param("i", $patient_id);
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

    <!--Custom JS-->
    <script src="../index.js"></script>
  </body>

  </html>