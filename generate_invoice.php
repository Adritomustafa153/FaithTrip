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

// Fetch Ticket Data
$ticketId = $_GET['ticket_id'] ?? null;

if (!$ticketId) {
    die("Ticket ID is required to generate an invoice.");
}

$sql = "SELECT 
            passenger_name,
            pnr,
            departure_date,
            ticket_number,
            price,
            airline_name,
            invoice_number,
            route,
            ticket_issue_date
        FROM tickets WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ticketId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("No ticket found for the given ID.");
}

$ticket = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Invoice Details
$invoiceNumber = $ticket['invoice_number'];
$currentDate = date("Y-m-d");

// Convert Amount to Words Function (as in the original code)
function convertToWords($number) {
    // Conversion logic here (omitted for brevity)
    return "One Thousand Two Hundred Thirty-Four"; // Example output
}

$amountInWords = convertToWords($ticket['price']);

// QR Code URL
$qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?data=Invoice%20No%20$invoiceNumber&size=150x150";

// Bar Code Image (Using a CDN for demonstration)
$barCodeUrl = "https://barcode.tec-it.com/barcode.ashx?data=$invoiceNumber&code=Code128&dpi=96";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - <?php echo $invoiceNumber; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }
        .invoice-container {
            width: 80%;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }
        .logo {
            width: 120px;
            margin-left: 20px;
            margin-top: 10px;
        }
        .company-info {
            text-align: right;
            background-color: #e9f2ff;
            padding: 10px;
            margin-right: 20px;
            margin-top: 10px;
            border-radius: 5px;
        }
        .company-info p {
            margin: 2px 0;
        }
        .invoice-title {
            text-align: center;
            font-size: 36px;
            color: #007bff;
            margin: 20px 0;
            text-transform: uppercase;
        }
        .bar-code {
            text-align: center;
            margin-top: -10px;
 
        }
        .bar-code img{
            width: 180px;
        }
        .details {
            margin-top: 10px;
        }
        .details p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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
        .amount-in-words {
            margin-top: 20px;
            background-color: #f9f9f9;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .we-accept {
            text-align: center;
            font-weight: bold;
            margin-top: 20px;
        }
        .payment-logos {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 10px;
            gap: 10px;
        }
        .payment-logos img {
            width: 60px;
            height: auto;
        }
        .qr-section {
            margin-top: 30px;
            margin-right: 30px;
            text-align: right;
        }
        .qr-section img {
            width: 100px;
            height: auto;
        }
        .qr-section p{
            margin-top: 5px;
            font-size: 12px;
            color: #555;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <img src="logo.jpg" alt="Company Logo" class="logo">
            <div class="company-info">
                <p><strong>Faith Travels and Tours Ltd</strong></p>
                <p>Abedin Tower, 35 Kamal Ataturk Avenue</p>
                <p>Banani, Dhaka 1213</p>
                <p>Email: director@faithtrip.net</p>
                <p>Phone: +8801617845236</p>
            </div>
        </div>

        <!-- Bar Code -->
        <div class="bar-code">
            <img src="<?php echo $barCodeUrl; ?>" alt="Bar Code">
        </div>

        <!-- Invoice Title -->
        <h1 class="invoice-title">Invoice</h1>

        <!-- Details -->
        <div class="details">
            <p><strong>Invoice No:</strong> <?php echo $invoiceNumber; ?></p>
            <p><strong>Date:</strong> <?php echo $currentDate; ?></p>
            <p><strong>Passenger Name:</strong> <?php echo htmlspecialchars($ticket['passenger_name']); ?></p>
            <p><strong>PNR:</strong> <?php echo htmlspecialchars($ticket['pnr']); ?></p>
            <p><strong>Flight Date:</strong> <?php echo htmlspecialchars($ticket['departure_date']); ?></p>
        </div>

        <!-- Ticket Table -->
        <table>
            <thead>
                <tr>
                    <th>Ticket Number</th>
                    <th>Route</th>
                    <th>Airline</th>
                    <th>Issue Date</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo htmlspecialchars($ticket['ticket_number']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['route']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['airline_name']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['ticket_issue_date']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['price']); ?> BDT</td>
                </tr>
            </tbody>
        </table>

        <!-- Amount in Words -->
        <div class="amount-in-words">
            <p><strong>Amount in Words:</strong> <?php echo $amountInWords; ?> Bangladeshi Taka Only</p>
        </div>

        <!-- We Accept -->
        <div class="we-accept">We Accept</div>
        <div class="payment-logos">
            <img src="https://logos-world.net/wp-content/uploads/2020/04/Visa-Symbol.png" alt="Visa">
            <img src="https://www.citypng.com/public/uploads/preview/hd-amex-american-express-logo-png-701751694708970jttzjjyo6e.png" alt="Amex">
            <img src="https://w7.pngwing.com/pngs/453/18/png-transparent-diners-club-international-credit-card-jcb-co-ltd-mastercard-business-credit-card-text-payment-logo.png" alt="Diners">
            <img src="https://th.bing.com/th/id/R.69a74822fc57c7d271371051ea7c20a2?rik=v6p8XLNqjw1QUA&pid=ImgRaw&r=0" alt="Discovery">
            <img src="https://th.bing.com/th/id/OIP.UsO2E6WkvMDPT9NK4y6VcAAAAA?w=400&h=242&rs=1&pid=ImgDetMain" alt="Mastercard">
            <img src="https://canada-first.ca/wp-content/uploads/2020/12/Union-Pay.png" alt="Union Pay">
            <img src="https://1.bp.blogspot.com/-r81coXfUPdw/YHkrJkiHK_I/AAAAAAAAefk/_Q59apgXnt4K9T3vdCeJpjNYRd7eZO04gCLcBGAsYHQ/s284/%25E0%25A6%25A8%25E0%25A7%258D%25E0%25A6%25AF%25E0%25A6%25BE%25E0%25A6%25B6%25E0%25A6%25A8%25E0%25A6%25BE%25E0%25A6%25B2%2B%25E0%25A6%25AA%25E0%25A7%2587%25E0%25A6%25AE%25E0%25A7%2587%25E0%25A6%25A8%25E0%25A7%258D%25E0%25A6%259F%2B%25E0%25A6%25B8%25E0%25A7%2581%25E0%25A6%2587%25E0%25A6%259A%2B%25E0%25A6%25AC%25E0%25A6%25BE%25E0%25A6%2582%25E0%25A6%25B2%25E0%25A6%25BE%25E0%25A6%25A6%25E0%25A7%2587%25E0%25A6%25B6%2B%2528NPSB%2529.jpeg" alt="NPSB">
            <img src="https://mir-s3-cdn-cf.behance.net/projects/404/e268d9164091167.Y3JvcCw3MTksNTYyLDkxNSw4OQ.png" alt="Nagad">
        </div>

        <!-- QR Code -->
        <div class="qr-section">
            <p>Scan the QR Code to get the soft copy of this invoice:</p>
            <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code">
        </div>

        <!-- Footer -->
        <div class="footer">
        <p><a href="banking_details.pdf" target="_blank">Click here to find the banking details</a></p>
        
            <p>© <?php echo date("Y"); ?> Faith Travels and Tours Ltd. All rights reserved.</p>

        </div>
    </div>
</body>
</html>
