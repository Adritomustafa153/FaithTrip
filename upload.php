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
        $pythonPath = "C:\Users\LENOVO\AppData\Local\Programs\Python\Python313\python.exe";
        $command = escapeshellcmd("$pythonPath extract_ticket_data.py " . $targetFilePath) . " 2>&1";
        $output = shell_exec($command);
        #echo "Command: " . $command . "<br>";
        echo "Output: " . nl2br($output) . "<br>";

        #$command = "python --version 2>&1";
        #$output = shell_exec($command);
        #echo "Python Version: " . nl2br($output);

        

        $data = json_decode($output, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "JSON Decode Error: " . json_last_error_msg();
            echo "Raw Output: " . $output;
            exit;
        }

        if ($data) {
            $ticketPrice = $_POST['ticket-price'];
            $passengerId = $_POST['passenger'];
            function convertToMySQLDate($dateString) {
                // Try different date formats
                $formats = ['d/m/Y', 'j M Y', 'Y-m-d']; // Add formats you expect
                foreach ($formats as $format) {
                    $date = DateTime::createFromFormat($format, $dateString);
                    if ($date !== false) {
                        return $date->format('Y-m-d'); // Convert to YYYY-MM-DD
                    }
                }
                throw new Exception("Invalid date format: $dateString");
            }
            
            
            // Example usage for your extracted data
            $departureDate = convertToMySQLDate($data['Departure Date']);
            $returnDate = convertToMySQLDate($data['Return Date']);
            $issueDate = convertToMySQLDate($data['Ticket Issue Date']);
            
            if (!$departureDate || !$returnDate || !$issueDate) {
                die("Invalid date format in the extracted data.");
            }

            // Save to Database
            $stmt = $conn->prepare(
                "INSERT INTO tickets (pnr, passenger_name, airline_name, departure_date, return_date, ticket_issue_date, ticket_number, price, Sales_Person) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            );

            $stmt->bind_param(
                "sssssssss",
                $data['PNR'],
                $data['Passenger Name'],
                $data['Airline Name'],
                #$data['Departure Date'],
                $departureDate,
                $returnDate,
                #$data['Return Date'],
                #$data['Ticket Issue Date'],
                $issueDate,
                $data['Ticket Number'],
                $ticketPrice,
                $passengerId
            );

            if ($stmt->execute()) {
                echo "Ticket details saved successfully.";
                #header("Location: generate_invoice.php?ticket_id=" . $conn->insert_id);
                header("Location: dashboard.php?ticket_id=" . $conn->insert_id);
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
