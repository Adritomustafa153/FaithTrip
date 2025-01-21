<?php
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'generateInvoice':
        include 'generate_invoice.php';
        break;

    case 'updateSpreadsheet':
        include 'update_spreadsheet.php';
        break;

    case 'sendEmail':
        include 'send_email.php';
        break;

    default:
        echo json_encode(['message' => 'Invalid action.']);
        break;
}
?>
