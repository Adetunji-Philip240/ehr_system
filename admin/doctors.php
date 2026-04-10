<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Admin') {
  header("Location: ../index.html");
  exit();
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Doctors</title>

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
  <!--Sidebar-->
  <div class="w3-sidebar w3-bar-block" style="width: 20%">
    <div class="w3-bar-item mt-5 mb-5">
      <img src="../images/logo.png" class="mx-auto d-block logo" alt="" />
    </div>
    <a href="dashboard.php" class="w3-bar-item py-3"><i class="fas fa-chart-line mx-3 fs-4"></i>DASHBOARD</a>
    <a style="
          background-color: var(--color3);
          color: var(--color2);
          border-radius: 0 10px 10px 0;
        " class="w3-bar-item py-3"><i class="fas fa-user-md mx-3 fs-4"></i>DOCTORS</a>
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
      <h3>Your Doctor(s)</h3>
      <div class="row mb-3">
        <div class="col-md-4">
          <input type="text" id="searchInput" class="form-control" placeholder="Search doctor...">
        </div>

        <div class="col-md-3">
          <select id="departmentFilter" class="form-control">
            <option value="">All Specialties</option>
            <option value="Cardiology">Cardiology</option>
            <option value="Neurology">Neurology</option>
            <option value="General">General</option>
          </select>
        </div>

        <div class="col-md-3">
          <select id="genderFilter" class="form-control">
            <option value="">All Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
          </select>
        </div>
      </div>


      <div class="table-responsive-sm">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>S/N</th>
              <th>Doctor ID</th>
              <th>Full Name</th>
              <th>Gender</th>
              <th>Specialty</th>
              <th>Department</th>
              <th>Phone</th>
              <th>Email</th>

              <!--(Active / On Leave / Suspended)-->
              <th>Patients</th>
              <!--Number of patients-->
              <th>Date Added</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="doctorTable">
            <?php
            require_once "../backend/db.php";

            $sql = "
  SELECT d.*, COUNT(DISTINCT a.patient_id) as total_patients
  FROM doctors d
  LEFT JOIN assignments a 
    ON d.id = a.doctor_id AND a.status = 'Active'
  GROUP BY d.id
  ";
            $result = $conn->query($sql);

            $sn = 1;

            while ($row = $result->fetch_assoc()) {
            ?>
            <tr>
              <td><?= $sn++ ?></td>
              <td><?= $row['doctor_code'] ?></td>
              <td><?= $row['full_name'] ?></td>
              <td><?= $row['gender'] ?></td>
              <td><?= $row['specialization'] ?></td>
              <td><?= $row['department'] ?></td>
              <td><?= $row['phone'] ?></td>
              <td><?= $row['email'] ?></td>
              <td><?= $row['total_patients'] ?></td>
              <td><?= date("d M Y", strtotime($row['date_joined'])) ?></td>
              <td>
                <a href="../backend/delete_doctor.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
                  onclick="return confirm('Are you sure you want to delete this doctor?');">
                  Delete
                </a>
                <a href="doctor_profile.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">
                  View
                </a>
              </td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>

      <nav>
        <ul class="pagination" id="pagination"></ul>
      </nav>

      <button class="btn btn-success mb-3 float-end" onclick="exportDoctorsPDF()">
        Export as PDF
      </button>
    </div>
  </div>


  <script>
  const rowsPerPage = 5;
  let currentPage = 1;

  const tableRows = Array.from(document.querySelectorAll("#doctorTable tr"));

  function filterRows() {
    let search = document.getElementById("searchInput").value.toLowerCase();
    let department = document.getElementById("departmentFilter").value.toLowerCase();
    let gender = document.getElementById("genderFilter").value.toLowerCase();

    return tableRows.filter(row => {
      let text = row.innerText.toLowerCase();
      let dept = row.cells[4].innerText.toLowerCase();
      let gen = row.cells[3].innerText.toLowerCase();

      return (
        text.includes(search) &&
        (department === "" || dept.includes(department)) &&
        (gender === "" || gen.includes(gender))
      );
    });
  }

  function displayTable() {
    let filtered = filterRows();

    tableRows.forEach(row => row.style.display = "none");

    let start = (currentPage - 1) * rowsPerPage;
    let end = start + rowsPerPage;

    filtered.slice(start, end).forEach(row => {
      row.style.display = "";
    });

    setupPagination(filtered.length);
  }

  function setupPagination(totalRows) {
    const pagination = document.getElementById("pagination");
    pagination.innerHTML = "";

    let pageCount = Math.ceil(totalRows / rowsPerPage);

    for (let i = 1; i <= pageCount; i++) {
      let li = document.createElement("li");
      li.className = "page-item " + (i === currentPage ? "active" : "");

      let btn = document.createElement("button");
      btn.className = "page-link";
      btn.innerText = i;

      btn.addEventListener("click", () => {
        currentPage = i;
        displayTable();
      });

      li.appendChild(btn);
      pagination.appendChild(li);
    }
  }

  // Event listeners
  document.getElementById("searchInput").addEventListener("keyup", () => {
    currentPage = 1;
    displayTable();
  });

  document.getElementById("departmentFilter").addEventListener("change", () => {
    currentPage = 1;
    displayTable();
  });

  document.getElementById("genderFilter").addEventListener("change", () => {
    currentPage = 1;
    displayTable();
  });

  // Initial load
  displayTable();
  </script>

  <!--Bootstrap-->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>


  <script>
  const loadWatermark = (src) => {
    return new Promise((resolve) => {
      const img = new Image();
      img.crossOrigin = "anonymous";

      img.onload = () => {
        const canvas = document.createElement("canvas");
        const ctx = canvas.getContext("2d");

        canvas.width = img.width;
        canvas.height = img.height;

        ctx.globalAlpha = 0.08; // 👈 faint watermark
        ctx.drawImage(img, 0, 0);

        resolve(canvas.toDataURL("image/png"));
      };

      img.src = src;
    });
  };
  </script>

  <script>
  async function exportDoctorsPDF() {
    const {
      jsPDF
    } = window.jspdf;
    const doc = new jsPDF("l", "mm", "a4");

    // =========================
    // LOAD IMAGES
    // =========================
    const loadImage = (src) => {
      return new Promise((resolve) => {
        const img = new Image();
        img.onload = () => resolve(img);
        img.src = src;
      });
    };

    const logo = await loadImage("../images/logo.png");
    const watermark = await loadWatermark("../images/logo.png");

    // =========================
    // HEADER
    // =========================
    const hospitalName = "Healix Hospital";
    const today = new Date().toLocaleDateString();

    doc.setFontSize(16);
    doc.text(hospitalName, 14, 15);

    doc.setFontSize(10);
    doc.text("Doctors List Report", 14, 22);
    doc.text("Date: " + today, 250, 15);

    doc.addImage(logo, "PNG", 200, 5, 20, 20);

    // =========================
    // TABLE DATA (FILTERED + ALL PAGES)
    // =========================
    let filtered = filterRows(); // uses your existing filter function

    let data = [];

    filtered.forEach((row, index) => {
      let cells = row.querySelectorAll("td");

      data.push([
        index + 1,
        cells[1].innerText,
        cells[2].innerText,
        cells[3].innerText,
        cells[4].innerText,
        cells[5].innerText,
        cells[6].innerText,
        cells[7].innerText,
        cells[8].innerText,
        cells[9].innerText
        // ❌ NO ACTION COLUMN (skipped)
      ]);
    });

    const headers = [
      "S/N",
      "Doctor ID",
      "Full Name",
      "Gender",
      "Specialty",
      "Department",
      "Phone",
      "Email",
      "Patients",
      "Date Joined"
    ];

    doc.autoTable({
      startY: 30,
      head: [headers],
      body: data,
      theme: "grid",
      styles: {
        fontSize: 8
      },
      headStyles: {
        fillColor: [0, 102, 204]
      },

      didDrawPage: function() {
        const pageWidth = doc.internal.pageSize.getWidth();
        const pageHeight = doc.internal.pageSize.getHeight();

        // Watermark
        doc.addImage(
          watermark,
          "PNG",
          pageWidth / 2 - 40,
          pageHeight / 2 - 40,
          80,
          80
        );
      }
    });

    // =========================
    // SIGNATURE SECTION
    // =========================
    let finalY = doc.lastAutoTable.finalY + 20;

    doc.line(200, finalY, 270, finalY);
    doc.setFontSize(10);
    doc.text("Admin Signature", 210, finalY + 5);
    doc.text("Authorized Signatory", 205, finalY + 12);

    // =========================
    // PAGE NUMBERS
    // =========================
    let pageCount = doc.internal.getNumberOfPages();

    for (let i = 1; i <= pageCount; i++) {
      doc.setPage(i);
      doc.text(
        `Page ${i} of ${pageCount}`,
        doc.internal.pageSize.getWidth() - 40,
        doc.internal.pageSize.getHeight() - 10
      );
    }

    doc.save("Doctors_List.pdf");
  }
  </script>


  <!--Custom JS-->
  <script src="../index.js"></script>
</body>

</html>