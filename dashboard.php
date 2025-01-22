<?php
$ticket_id = $_GET['ticket_id'] ?? null;

// Database Connection
$host = "localhost";
$username = "root";
$password = "";
$database = "faithtrip";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM tickets where id=?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$result = $stmt->get_result();
$ticket = $result->fetch_assoc();
$stmt->close();
$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="dashboard.js" defer></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }
        .container {
            width: 90%;
            margin: 20px auto;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .buttons {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
        }
        button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        button:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
        }
        table th, table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        table th {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>


<div class="container">
        <h1>Dashboard</h1>
        <table>
            <thead>
                <tr>
                    <th>Passenger Name</th>
                    <th>PNR</th>
                    <th>Flight Date</th>
                    <th>Ticket Number</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody id="ticket-datas">
                <!-- Populated dynamically using PHP -->
                 <tr>
                    <td><?php echo $ticket['passenger_name'] ?></td>
                    <td><?php echo $ticket['pnr'] ?></td>
                    <td><?php echo $ticket['departure_date'] ?></td>
                    <td><?php echo $ticket['ticket_number'] ?></td>
                    <td><?php echo $ticket['price'] ?></td>
                 </tr>
            </tbody>
        </table>    

    <!-- Embed ticket_id as a data attribute -->
    <div id="ticket-data" data-ticket-id="<?php echo htmlspecialchars($ticket_id); ?>"></div>

    <!-- Buttons -->
    <button id="generate-invoice">Generate Invoice</button>
    <button id="send-to-spreadsheet">Send to Spreadsheet</button>
    <button id="send-email">Send Email</button>
    </div>
</body>
</html>
