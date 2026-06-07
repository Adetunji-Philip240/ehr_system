<?php


session_start();

require_once "db.php";



// =========================
// CHECK IF FORM WAS SUBMITTED
// =========================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  die("Invalid request.");
}

// =========================
// GET FORM DATA
// =========================
$patient_id = $_POST['patient_id'] ?? '';
$doctor_id = $_POST['doctor_id'] ?? '';

$reason = $_POST['reason_for_visit'] ?? '';
$complaint = $_POST['chief_complaint'] ?? '';
$symptoms = $_POST['symptoms'] ?? '';
$vitals = $_POST['vital_signs'] ?? '';
$diagnosis = $_POST['diagnosis'] ?? '';
$treatment = $_POST['treatment'] ?? '';
$prescription = $_POST['prescription'] ?? '';
$notes = $_POST['doctor_notes'] ?? '';

// =========================
// GENERATE RECORD CODE
// =========================
$record_code = "REC" . rand(10000, 99999);

// =========================
// INSERT INTO DATABASE
// =========================
$stmt = $conn->prepare("
    INSERT INTO records 
    (
        patient_id,
        doctor_id,
        record_code,
        reason,
        complaint,
        symptoms,
        vitals,
        diagnosis,
        treatment,
        prescription,
        notes
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
  "iisssssssss",
  $patient_id,
  $doctor_id,
  $record_code,
  $reason,
  $complaint,
  $symptoms,
  $vitals,
  $diagnosis,
  $treatment,
  $prescription,
  $notes
);

$stmt->execute();

$record_id = $stmt->insert_id;

// =========================
// GET PATIENT DETAILS
// =========================
$stmt = $conn->prepare("SELECT * FROM patients WHERE id=?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();

// =========================
// GET DOCTOR DETAILS
// =========================
$stmt = $conn->prepare("SELECT * FROM doctors WHERE id=?");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();

// =========================
// DATE OF SUBMISSION
// =========================
$date = date("d M Y, h:i A");

// =========================
// GENERATE PDF FILE
// =========================
require_once "../backend/fpdf/fpdf.php";

$pdf = new FPDF();
$pdf->AddPage();

// =========================
// HEADER (HOSPITAL INFO)
// =========================
$pdf->Image('../images/logo.png', 10, 8, 25);

$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'HEALIX HOSPITAL - MEDICAL REPORT', 0, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 5, 'Confidential Patient Medical Record', 0, 1, 'C');

$pdf->Ln(8);

// =========================
// PATIENT + DOCTOR INFO BOX
// =========================
$pdf->SetFont('Arial', '', 11);

// Patient Info
$pdf->Cell(95, 8, "Patient Name: " . $patient['full_name'], 1, 0);
$pdf->Cell(95, 8, "Patient Code: " . $patient['patient_code'], 1, 1);

// Doctor Info
$pdf->Cell(95, 8, "Doctor Name: " . $doctor['full_name'], 1, 0);
$pdf->Cell(95, 8, "Doctor Code: " . $doctor['doctor_code'], 1, 1);

// Date
$pdf->Cell(190, 8, "Date of Submission: " . $date, 1, 1);

$pdf->Ln(5);

// =========================
// RECORD DETAILS SECTION
// =========================
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, "MEDICAL INFORMATION", 0, 1);

$pdf->SetFont('Arial', '', 11);

function row($pdf, $label, $value)
{
  $pdf->Cell(50, 8, $label, 1);
  $pdf->Cell(140, 8, $value, 1, 1);
}

row($pdf, "Record Code", $GLOBALS['record_code']);
row($pdf, "Reason", $GLOBALS['reason']);
row($pdf, "Complaint", $GLOBALS['complaint']);
row($pdf, "Symptoms", $GLOBALS['symptoms']);
row($pdf, "Vitals", $GLOBALS['vitals']);
row($pdf, "Diagnosis", $GLOBALS['diagnosis']);
row($pdf, "Treatment", $GLOBALS['treatment']);
row($pdf, "Prescription", $GLOBALS['prescription']);
row($pdf, "Doctor Notes", $GLOBALS['notes']);

$pdf->Ln(10);

// =========================
// SIGNATURE SECTION
// =========================
$pdf->Cell(95, 10, "Signature:", 0, 0);

// Signature image
$pdf->Image('../backend/signature/sign.png', 20, $pdf->GetY() + 10, 40);

$pdf->Ln(25);

// Line under signature
$pdf->Cell(95, 10, "____________________", 0, 0);

// =========================
// FOOTER
// =========================
$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 10, "This document is system-generated and confidential.", 0, 1, 'C');
// =========================
// SUCCESS MESSAGE
// =========================
$_SESSION['msg'] = "Record saved successfully!";

$uploadDir = __DIR__ . "/../uploads/";

if (!file_exists($uploadDir)) {
  mkdir($uploadDir, 0777, true);
}

$filePath = $uploadDir . $record_id . ".pdf";

$pdf->Output("F", $filePath);

// =========================
// REDIRECT BACK
// =========================
header("Location: ../doctor/patient_profile.php?id=" . $patient_id);
exit;
