<?php
session_start();
require"db.php"; // Include database connection

// Fetch order number from query string or session
$orderNumber = $_GET['order_number'] ?? $_SESSION['order_number'] ?? null;

if (!$orderNumber) {
    die("Error: Order number not provided.");
}

// Calculate the total bill amount
$stmt = $conn->prepare("
    SELECT SUM(dt.quantity * m.price) AS total
    FROM order_details dt
    INNER JOIN menu m ON dt.food_item = m.name
    WHERE dt.order_number = ?
");
$stmt->bind_param("s", $orderNumber);
$stmt->execute();
$stmt->bind_result($totalAmount);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect user input
    $username = $_POST['username'];
    $contactNumber = $_POST['contact_number'];
    $paymentMode = $_POST['payment_mode'];
    $paymentStatus = 'Unpaid'; // Default payment status

    // Insert user details into the `users` table
    $stmt = $conn->prepare("
        INSERT INTO users (username, contact_number, order_number, total_bill_amount, payment_mode, payment_status)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssdss", $username, $contactNumber, $orderNumber, $totalAmount, $paymentMode, $paymentStatus);

    if ($stmt->execute()) {
        // Update the name and contact number in the `bill` table
        $updateStmt = $conn->prepare("
            UPDATE bill
            SET username = ?, contact_number = ?
            WHERE order_number = ?
        ");
        $updateStmt->bind_param("sss", $username, $contactNumber, $orderNumber);

        if ($updateStmt->execute()) {
            // Redirect to the bill page
            header("Location: bill.php?order_number=" . urlencode($orderNumber));
            exit;
        } else {
            die('Error updating bill table: ' . $updateStmt->error);
        }
        // $updateStmt->close();
    } else {
        die('Database error: ' . $stmt->error);
    }
    // $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <style>
        /* Add your CSS here */
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        h1, h2, p {
            text-align: center;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        input, select, button {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .bill {
            margin-top: 20px;
        }
        .bill table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .bill th, .bill td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .bill th {
            background-color: #28a745;
            color: white;
        }
        .qr-code {
            text-align: center;
            margin-top: 20px;
        }
        .qr-code img {
            width: 200px;
        }

    </style>
</head>
<body>
    <div class="container">
        <h1>Enter Your Details</h1>
        <form method="POST">
            <label for="username">Name:</label>
            <input type="text" id="username" name="username" required>

            <label for="contact_number">Contact Number:</label>
            <input type="text" id="contact_number" name="contact_number" pattern="[0-9]{10}" title="Enter a valid 10-digit number" required>

            <label for="payment_mode">Payment Mode:</label>
            <select id="payment_mode" name="payment_mode" required>
                <option value="">Select Payment Mode</option>
                <option value="Online Payment">Online Payment</option>
                <option value="Pay on Desk">Pay on Desk</option>
            </select>

            <button type="submit">Submit</button>
        </form>
    </div>
</body>
</html>
