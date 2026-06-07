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
  <title>Patients</title>

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
    <a href="doctors.php" class="w3-bar-item py-3"><i class="fas fa-user-md mx-3 fs-4"></i>DOCTORS</a>
    <a style="
          background-color: var(--color3);
          color: var(--color2);
          border-radius: 0 10px 10px 0;
        " class="w3-bar-item py-3"><i class="fas fa-user mx-3 fs-4"></i>PATIENTS</a>
  </div>

  <!--Page Content-->
  <div class="page-content">
    <!--Top Bar-->
    <div class="top-bar d-flex justify-content-between p-4 fs-3">
      <li>Hello Admin</li>
      <li>
        <a href="../backend/logout.php"><i class="fas fa-sign-out-alt"></i></a>
      </li>
    </div>

    <!--Section 1-->
    <div class="mt-5 container">
      <h3>Your Patient(s)</h3>
      <div class="row mb-3">
        <div class="col-md-4">
          <input type="text" id="searchInput" class="form-control" placeholder="Search patient...">
        </div>

        <div class="col-md-3">
          <select id="genderFilter" class="form-control">
            <option value="">All Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
          </select>
        </div>
      </div>

      <div id="exportArea">
        <div class="table-responsive-sm">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>S/N</th>
                <th>Patient ID</th>
                <th>Full Name</th>
                <th>Gender</th>
                <th>DOB</th>
                <th>Address</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Records</th>
                <th>Date Added</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="patientTable">
              <?php
              require_once "../backend/db.php";

              $sql = "
SELECT p.*, 
(SELECT COUNT(*) FROM records r WHERE r.patient_id = p.id) AS total_records
FROM patients p
";
              $result = $conn->query($sql);

              $sn = 1;

              while ($row = $result->fetch_assoc()) {
              ?>
                <tr>
                  <td><?= $sn++ ?></td>
                  <td><?= $row['patient_code'] ?></td>
                  <td><?= $row['full_name'] ?></td>
                  <td class="gender"><?= $row['gender'] ?></td>
                  <td><?= date("d M Y", strtotime($row['dob'])) ?></td>
                  <td><?= $row['address'] ?></td>
                  <td><?= $row['phone'] ?></td>
                  <td><?= $row['email'] ?></td>
                  <td><?= $row['total_records'] ?></td>
                  <td><?= date("d M Y", strtotime($row['date_added'])) ?></td>
                  <td>
                    <a href="../backend/delete_patient.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
                      onclick="return confirm('Are you sure you want to delete this patient?');">
                      Delete
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
      </div>
      <nav class="mt-3">
        <ul class="pagination" id="pagination"></ul>
      </nav>


      <button onclick="exportPDF()" class="btn btn-success mb-3 float-end">
        Export PDF
      </button>
    </div>
  </div>


  <div style="margin-top: 150px"></div>
  <!--Bottom Navbar-->

  <div id="bottom-navbar" class="navbar navbar-expand-sm fixed-bottom d-lg-none d-md-none">
    <div class="container-fluid d-flex justify-content-between">
      <a " href=" dashboard.php"><i class="fas fa-chart-line mx-3 fs-4"></i> <br /><span
          style="font-size: 10px">DASHBOARD</span></a>

      <a href="doctors.php"><i class=" fas fa-user-md mx-3 fs-4"></i> <br /><span
          style="font-size: 10px">DOCTORS</span></a>

      <a style="
            border: 1px solid var(--color2);
            color: var(--color2);
            padding: 5px;
            border-radius: 10px;"><i class="fas fa-user mx-3 fs-4"></i> <br /><span
          style="font-size: 10px">PATIENTS</span></a>
    </div>
  </div>





  <script>
    const rowsPerPage = 5;
    let currentPage = 1;

    const tableRows = Array.from(document.querySelectorAll("#patientTable tr"));

    function filterRows() {
      let search = document.getElementById("searchInput").value.toLowerCase();
      let gender = document.getElementById("genderFilter").value.toLowerCase();

      return tableRows.filter(row => {
        let text = row.innerText.toLowerCase();
        let gen = row.querySelector(".gender").innerText.toLowerCase();

        return (
          text.includes(search) &&
          (gender === "" || gen === gender)
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

    document.getElementById("genderFilter").addEventListener("change", () => {
      currentPage = 1;
      displayTable();
    });

    // Initial load
    displayTable();
  </script>

  <!--Bootstrap-->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


  <!--Export as PDF-->


  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

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

          // 👉 reduce opacity
          ctx.globalAlpha = 0.08; // 👈 CHANGE THIS (0.05 - 0.2 best range)

          ctx.drawImage(img, 0, 0);

          resolve(canvas.toDataURL("image/png"));
        };

        img.src = src;
      });
    };
  </script>

  <script>
    async function exportPDF() {
      const {
        jsPDF
      } = window.jspdf;
      const doc = new jsPDF("l", "mm", "a4");

      // =========================
      // LOAD IMAGE PROPERLY
      // =========================
      const loadImage = (src) => {
        return new Promise((resolve) => {
          const img = new Image();
          img.src = src;
          img.onload = () => resolve(img);
        });
      };

      const img = await loadImage("../images/logo.png");


      const watermark = await loadWatermark("../images/logo.png");

      // Add logo AFTER loading
      doc.addImage(img, "PNG", 200, 5, 20, 20);

      // =========================
      // HEADER
      // =========================
      const hospitalName = "HEALIX HOSPITAL";
      const today = new Date().toLocaleDateString();

      doc.setFontSize(16);
      doc.text(hospitalName, 14, 15);

      doc.setFontSize(10);
      doc.text("Patient List", 14, 22);
      doc.text("Date: " + today, 250, 15);

      // =========================
      // TABLE
      // =========================
      const headers = [
        "S/N", "Patient ID", "Full Name", "Gender", "DOB",
        "Address", "Phone", "Email", "Records", "Date Added"
      ];

      let filtered = filterRows();

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
        ]);
      });



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


      let finalY = doc.lastAutoTable.finalY + 20;

      // Signature line
      doc.line(200, finalY, 270, finalY);

      // Text
      doc.setFontSize(10);
      doc.text("Admin Signature", 210, finalY + 5);
      doc.text("Authorized Signatory", 205, finalY + 12);

      // =========================
      // PAGE NUMBERS
      // =========================
      let pageCount = doc.internal.getNumberOfPages();

      for (let i = 1; i <= pageCount; i++) {
        doc.setPage(i);
        doc.setFontSize(10);
        doc.text(
          "Page " + i + " of " + pageCount,
          doc.internal.pageSize.getWidth() - 40,
          doc.internal.pageSize.getHeight() - 10
        );
      }

      doc.save("Patients_list.pdf");
    }
  </script>

  <!--Custom JS-->
  <script src="../index.js"></script>
</body>

</html>