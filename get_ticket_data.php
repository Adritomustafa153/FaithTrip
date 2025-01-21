<?php
header('Content-Type: application/json');
include 'includes/db_connection.php';

// Fetch ticket data from the database
$result = $conn->query("SELECT * FROM tickets");
$tickets = [];

while ($row = $result->fetch_assoc()) {
    $tickets[] = $row;
}

// Example invoice number
$invoice_no = '$invoice_no';

echo json_encode([
    'invoice_no' => $invoice_no,
    'tickets' => $tickets
]);
?>
