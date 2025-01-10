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

// Fetch Passengers Information
$sql = "SELECT id, full_name FROM passengers";
$result = $conn->query($sql);

$passengers = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $passengers[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passenger Information</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Passenger Information</h1>

    <form action="upload.php" method="POST" enctype="multipart/form-data">
        <label for="passenger">Select Passenger:</label>
        <select name="passenger" id="passenger" required>
            <option value="">-- Select Passenger --</option>
            <?php foreach ($passengers as $passenger): ?>
                <option value="<?php echo $passenger['id']; ?>">
                    <?php echo $passenger['full_name']; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="ticket-price">Ticket Price:</label>
        <input type="number" name="ticket-price" id="ticket-price" required>

        <label for="ticket-file">Upload Ticket:</label>
        <input type="file" name="ticket-file" id="ticket-file" accept="application/pdf" required>

        <button type="submit">Submit</button>
    </form>
</body>
</html>
