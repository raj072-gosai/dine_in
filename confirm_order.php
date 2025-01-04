<?php
session_start();
require"db.php";  // Include database connection

// Ensure table number is passed and valid
$tableNumber = $_GET['table_number'] ?? null;
if (!$tableNumber) {
    die("Table number is required.");
}

// If no order number exists for this table, create a new one
if (!isset($_SESSION['order_number_' . $tableNumber])) {
    $_SESSION['order_number_' . $tableNumber] = 'ORDER-' . uniqid();
}

$orderNumber = $_SESSION['order_number_' . $tableNumber];

// Insert cart items into the display_table (order items for the table)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_SESSION['cart_' . $tableNumber]) && !empty($_SESSION['cart_' . $tableNumber])) {
        $cartItems = $_SESSION['cart_' . $tableNumber];

        foreach ($cartItems as $item) {
            $sql = "INSERT INTO display_table (order_number, table_number, food_item, quantity, price) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die('Prepare failed: ' . $conn->error);
            }
            $stmt->bind_param("siisi", $orderNumber, $tableNumber, $item['name'], $item['quantity'], $item['price']);
            $stmt->execute();
            if ($stmt->error) {
                die('Execute failed: ' . $stmt->error);
            }
        }

        unset($_SESSION['cart_' . $tableNumber]);  // Clear cart after confirmation
        $_SESSION['view_bill_enabled_' . $tableNumber] = true;  // Enable "View Bill"

        // Redirect back to cart with success message
        header("Location: cart.php?table_number=$tableNumber&status=success");
        exit;
    } else {
        echo "Your cart is empty. Please add items before confirming.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Order</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 20px;
        }
        .container {
            margin: auto;
            max-width: 600px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        .button {
            padding: 10px 20px;
            margin-top: 20px;
            font-size: 16px;
            color: white;
            background: #28a745;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .button:hover {
            background: #218838;
        }
    </style>
    <script>
        // Alert box for confirmation
        function confirmOrder() {
            const userConfirmed = confirm("Are you sure you want to place this order? Once placed, it cannot be changed or cancelled.");
            if (userConfirmed) {
                // Submit the form by triggering the POST request
                fetch(window.location.href, { method: "POST" })
                    .then(response => response.text())
                    .then(() => {
                        alert("Order placed successfully!");
                        window.location.href = `cart.php?table_number=${<?php echo $tableNumber; ?>}`;
                    })
                    .catch(error => {
                        alert("Something went wrong! Please try again.");
                        console.error(error);
                    });
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Confirm Order</h1>
        <p><strong>Table Number:</strong> <?php echo $tableNumber; ?></p>
        <p><strong>Order Number:</strong> <?php echo $orderNumber; ?></p>

        <button class="button" onclick="confirmOrder()">Place Order</button>
    </div>
</body>
</html>
