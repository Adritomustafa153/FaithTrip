<?php
require 'vendor/autoload.php'; // Ensure PHPMailer is installed via Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

    // Send email
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.your-email-provider.com'; // Replace with your SMTP host
        $mail->SMTPAuth = true;
        $mail->Username = 'your-email@example.com'; // Replace with your email
        $mail->Password = 'your-email-password'; // Replace with your email password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('your-email@example.com', 'FaithTrip');
        $mail->addAddress($ticket['email']); // Ensure 'email' exists in your tickets table

        $mail->isHTML(true);
        $mail->Subject = 'Your Ticket Invoice';
        $mail->Body = 'Please find your invoice attached.';

        $filePath = "invoices/invoice_" . $ticketId . ".pdf";
        $mail->addAttachment($filePath);

        $mail->send();

        echo json_encode(['message' => 'Email sent successfully.']);
    } catch (Exception $e) {
        echo json_encode(['message' => 'Email could not be sent. Error: ' . $mail->ErrorInfo]);
    }
} else {
    echo json_encode(['message' => 'Ticket not found.']);
}
?>
