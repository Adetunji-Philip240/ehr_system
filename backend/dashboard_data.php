<?php
require_once "db.php";

// 1. Doctor vs Assignments
$doctorData = [];
$doctorLabels = [];

$sql1 = "
SELECT d.full_name, d.doctor_code, COUNT(a.id) as total
FROM doctors d
LEFT JOIN assignments a ON d.id = a.doctor_id
GROUP BY d.id
";

$result1 = $conn->query($sql1);

while ($row = $result1->fetch_assoc()) {
  $doctorLabels[] = $row['full_name'] . " (" . $row['doctor_code'] . ")";
  $doctorData[] = $row['total'];
}

// 2. Patient vs Records
$patientData = [];
$patientLabels = [];

$sql2 = "
SELECT p.full_name, p.patient_code, COUNT(r.id) as total
FROM patients p
LEFT JOIN records r ON p.id = r.patient_id
GROUP BY p.id
";

$result2 = $conn->query($sql2);

while ($row = $result2->fetch_assoc()) {
  $patientLabels[] = $row['full_name'] . " (" . $row['patient_code'] . ")";
  $patientData[] = $row['total'];
}

// Return JSON
echo json_encode([
  "doctorLabels" => $doctorLabels,
  "doctorData" => $doctorData,
  "patientLabels" => $patientLabels,
  "patientData" => $patientData
]);
