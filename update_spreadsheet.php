<?php
require 'vendor/autoload.php'; // Ensure Google API Client is installed via Composer

use Google\Client;
use Google\Service\Sheets;

$ticketId = $_GET['ticket_id'] ?? null;

if (!$ticketId) {
    echo json_encode(['message' => 'Ticket ID is required.']);
    exit;
}

include 'includes/db_connection.php';

// Fetch ticket data
$stmt = $conn->prepare("SELECT * FROM tickets WHERE id = ?");
$stmt->bind_param("i", $ticketId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $ticket = $result->fetch_assoc();

    // Google Sheets API setup
    $client = new Client();
    $client->setApplicationName('FaithTrip Ticket System');
    $client->setScopes(Sheets::SPREADSHEETS);
    $client->setAuthConfig('credentials.json');
    $service = new Sheets($client);

    $spreadsheetId = 'YOUR_SPREADSHEET_ID'; // Replace with your spreadsheet ID
    $range = 'Sheet1!A1'; // Adjust range as needed

    $values = [
        array_values($ticket),
    ];
    $body = new Sheets\ValueRange(['values' => $values]);

    $params = ['valueInputOption' => 'RAW'];
    $service->spreadsheets_values->append($spreadsheetId, $range, $body, $params);

    echo json_encode(['message' => 'Data updated in spreadsheet successfully.']);
} else {
    echo json_encode(['message' => 'Ticket not found.']);
}
?>
