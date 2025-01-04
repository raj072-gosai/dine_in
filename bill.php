<?php
session_start();
require"db.php"; // Include database connection

// Fetch order number
$orderNumber = $_GET['order_number'] ?? null;

if (!$orderNumber) {
    die("Error: Order number not provided.");
}

// Fetch user and bill details
$stmt = $conn->prepare("
    SELECT username, contact_number, total_bill_amount, payment_mode
    FROM users
    WHERE order_number = ?
");
$stmt->bind_param("s", $orderNumber);
$stmt->execute();
$stmt->bind_result($username, $contactNumber, $totalAmount, $paymentMode);
$stmt->fetch();
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill</title>
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
    <script>
        setTimeout(() => {
            window.location.href = "thank_you.php";
        }, 30000);
    </script>
</head>
<body>
    <div class="container">
        <h1>Bill</h1>
        <h2>Order Number: <?php echo htmlspecialchars($orderNumber); ?></h2>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($username); ?></p>
        <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($contactNumber); ?></p>
        <p><strong>Payment Mode:</strong> <?php echo htmlspecialchars($paymentMode); ?></p>

        <div class="bill">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Price (₹)</th>
                        <th>Total (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch order details to display
                    $stmt = $conn->prepare("
                        SELECT dt.food_item, dt.quantity, m.price
                        FROM order_details dt
                        INNER JOIN menu m ON dt.food_item = m.name
                        WHERE dt.order_number = ?
                    ");
                    $stmt->bind_param("s", $orderNumber);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    while ($row = $result->fetch_assoc()):
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['food_item']); ?></td>
                            <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                            <td><?php echo htmlspecialchars(number_format($row['price'], 2)); ?></td>
                            <td><?php echo htmlspecialchars(number_format($row['quantity'] * $row['price'], 2)); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <p><strong>Total Bill Amount: ₹<?php echo number_format($totalAmount, 2); ?></strong></p>
        </div>

        <?php if ($paymentMode === 'Online Payment'): ?>
            <div class="qr-code">
                <p>Scan this QR code to pay:</p>
                <img src="images/qr.jpg" alt="QR Code">
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
