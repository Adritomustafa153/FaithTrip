<?php
include 'includes/db_connection.php';

$ticketId = $_GET['ticket_id'] ?? null;

if (!$ticketId) {
    echo json_encode(['message' => 'Ticket ID is required.']);
    exit;
}

// Fetch ticket data
$stmt = $conn->prepare("SELECT * FROM tickets WHERE id = ?");
$stmt->bind_param("i", $ticketId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $ticket = $result->fetch_assoc();

    // Generate Invoice PDF (using FPDF or TCPDF)
    $fileName = "invoices/invoice_" . $ticketId . ".pdf";

    require('fpdf/fpdf.php'); // Ensure FPDF is installed and available
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Invoice', 1, 1, 'C');
    $pdf->Ln(10);

    $pdf->SetFont('Arial', '', 12);
    foreach ($ticket as $key => $value) {
        $pdf->Cell(0, 10, ucfirst($key) . ': ' . $value, 0, 1);
    }

    $pdf->Output('F', $fileName);

    echo json_encode(['message' => 'Invoice generated successfully.', 'file' => $fileName]);
} else {
    echo json_encode(['message' => 'Ticket not found.']);
}
?>
