<?php
session_start();
require"db.php";  // Include the database connection

// Ensure table number is passed and valid
$tableNumber = $_GET['table_number'] ?? null;
if (!$tableNumber) {
    die("Table number is required.");
}

// Check if the order number exists for this table in the session
if (!isset($_SESSION['order_number_' . $tableNumber])) {
    die("You must confirm the order before viewing the bill.");
}

$orderNumber = $_SESSION['order_number_' . $tableNumber];

// Query to fetch order details for the given table and order number
$sql = "
    SELECT 
        food_item, 
        quantity, 
        price 
    FROM 
        order_details 
    WHERE 
        table_number = ? AND order_number = ?";
$stmt = $conn->prepare($sql);

// Check if prepare() failed
if ($stmt === false) {
    die('MySQL prepare error: ' . $conn->error);
}

$stmt->bind_param("is", $tableNumber, $orderNumber);
$stmt->execute();
$result = $stmt->get_result();

// Prepare the order details
$orderDetails = [];
$totalAmount = 0;
while ($row = $result->fetch_assoc()) {
    $orderDetails[] = $row;
    $totalAmount += $row['quantity'] * $row['price'];  // Calculate the total
}

// Fetch username and user contact from the `users` table
$userSql = "SELECT username, contact_number FROM users WHERE order_number = ?";
$stmt = $conn->prepare($userSql);
if (!$stmt) {
    die("MySQL error: " . $conn->error);
}
$stmt->bind_param("s", $orderNumber);
$stmt->execute();
$userResult = $stmt->get_result()->fetch_assoc();
$username = $userResult['username'] ?? 'Unknown';
$contactNumber = $userResult['contact_number'] ?? 'Unknown';

// Insert data into the `bill` table if not already inserted
$billInsertSql = "
    INSERT INTO bill (order_number, username, contact_number, total_bill_amount, payment_status)
    VALUES (?, ?, ?, ?, 'Unpaid')
    ON DUPLICATE KEY UPDATE 
        username = VALUES(username),
        contact_number = VALUES(contact_number),
        total_bill_amount = VALUES(total_bill_amount),
        updated_at = CURRENT_TIMESTAMP
";
$stmt = $conn->prepare($billInsertSql);
if (!$stmt) {
    die("MySQL error: " . $conn->error);
}
$stmt->bind_param("sssd", $orderNumber, $username, $contactNumber, $totalAmount);
if (!$stmt->execute()) {
    die("Error inserting bill: " . $stmt->error);
}
if (isset($_GET['destroy_session'])) {
    // Destroy the session for the specific table
    unset($_SESSION['order_number_' . $tableNumber]);
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Bill</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 8000px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1, p {
            text-align: center;
        }
        table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #28a745;
            color: white;
        }
        .total {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
        }
        .button {
            display: inline-block;
            margin: 20px 10px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .checkout-button {
            background-color: #dc3545;
        }
        .checkout-button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Bill - Table <?php echo htmlspecialchars($tableNumber); ?></h1>

        <!-- Order Number and Date -->
        <p><strong>Order Number:</strong> <?php echo htmlspecialchars($orderNumber); ?></p>
        <p><strong>Date:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>

        <!-- Order Details -->
        <table>
            <thead>
                <tr>
                    <th>Food Item</th>
                    <th>Quantity</th>
                    <th>Price (₹)</th>
                    <th>Total (₹)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orderDetails as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['food_item']); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td>₹<?php echo htmlspecialchars(number_format($item['price'], 2)); ?></td>
                        <td>₹<?php echo htmlspecialchars(number_format($item['quantity'] * $item['price'], 2)); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p class="total">Total Amount: ₹<?php echo number_format($totalAmount, 2); ?></p>

        <!-- Buttons Section -->
        <div style="text-align: center;">
            <!-- Back to Menu Button -->
            <a href="menu.php" class="button">Back to Menu</a>

            <!-- Checkout Button -->
            <a href="checkout.php?table_number=<?php echo htmlspecialchars($tableNumber); ?>&order_number=<?php echo htmlspecialchars($orderNumber); ?>" 
               class="button checkout-button">Checkout</a>
        </div>
    </div>
</body>
</html>
