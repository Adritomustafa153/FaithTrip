<?php
// Database Connection
$host = "localhost";
$username = "root";
$password = "";
$database = "faithtrip";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle File Upload
    $targetDir = "uploads/";
    $fileName = basename($_FILES["ticket-file"]["name"]);
    $targetFilePath = $targetDir . $fileName;

    if (move_uploaded_file($_FILES["ticket-file"]["tmp_name"], $targetFilePath)) {
        // Extract Data using Python Script
        $command = escapeshellcmd("python extract_ticket_data.py " . $targetFilePath);
        $output = shell_exec($command);

        $data = json_decode($output, true);
        if ($data) {
            $ticketPrice = $_POST['ticket-price'];
            $passengerId = $_POST['passenger'];

            // Save to Database
            $stmt = $conn->prepare(
                "INSERT INTO tickets (pnr, passenger_name, airline_name, departure_date, return_date, issue_date, ticket_number, price, passenger_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssssssssi",
                $data['PNR'],
                $data['Passenger Name'],
                $data['Airline Name'],
                $data['Departure Date'],
                $data['Return Date'],
                $data['Ticket Issue Date'],
                $data['Ticket Number'],
                $ticketPrice,
                $passengerId
            );

            if ($stmt->execute()) {
                echo "Ticket details saved successfully.";
                header("Location: generate_invoice.php?ticket_id=" . $conn->insert_id);
                exit;
            } else {
                echo "Error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "Failed to extract ticket data.";
        }
    } else {
        echo "File upload failed.";
    }
}

$conn->close();
?>
