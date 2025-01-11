<?php
session_start();
require "db.php";

$tableNumber = isset($_GET['table_number']) ? intval($_GET['table_number']) : null;
if (!$tableNumber) {
    die("Table number is required.");
}

// Check if an order number already exists in the session for the table, if not, generate one
if (!isset($_SESSION['order_number_' . $tableNumber])) {
    $_SESSION['order_number_' . $tableNumber] = 'ORD' . time() . rand(1000, 9999);
}
$orderNumber = $_SESSION['order_number_' . $tableNumber];

// Fetch cart items
$cartSql = "SELECT c.id, c.menu_id, c.quantity, m.name, m.price 
            FROM cart c
            INNER JOIN menu m ON c.menu_id = m.id
            WHERE c.table_number = ?";
$stmt = $conn->prepare($cartSql);
$stmt->bind_param("i", $tableNumber);
$stmt->execute();
$cartItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch orders placed for the table
$orderSql = "SELECT o.food_item, o.quantity, o.price
             FROM orders o
             WHERE o.order_number = ?";
$stmt = $conn->prepare($orderSql);
$stmt->bind_param("s", $orderNumber);
$stmt->execute();
$ordersPlaced = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle Generate Bill button
if (isset($_GET['generate_bill'])) {
    // Copy orders to order_details
    $copySql = "INSERT INTO order_details (table_number, order_number, food_item, quantity, price)
                SELECT table_number, order_number, food_item, quantity, price
                FROM orders
                WHERE order_number = ?";
    $stmt = $conn->prepare($copySql);
    $stmt->bind_param("s", $orderNumber);
    if (!$stmt->execute()) {
        die("Error copying orders to order_details: " . $stmt->error);
    }

    // Delete orders for the current order number
    $deleteOrdersSql = "DELETE FROM orders WHERE order_number = ?";
    $stmt = $conn->prepare($deleteOrdersSql);
    $stmt->bind_param("s", $orderNumber);
    if (!$stmt->execute()) {
        die("Error deleting orders: " . $stmt->error);
    }

    echo "<script>alert('Bill Generated Successfully!');</script>";
    header("Location: view_bill.php?table_number=$tableNumber");
    exit;
}

// Handle Place Order button
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    foreach ($cartItems as $item) {
        // Insert into orders table
        $insertOrderSql = "INSERT INTO orders (table_number, order_number, food_item, quantity, price) 
                           VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertOrderSql);
        $stmt->bind_param("issid", $tableNumber, $orderNumber, $item['name'], $item['quantity'], $item['price']);
        if (!$stmt->execute()) {
            die("Error inserting into orders: " . $stmt->error);
        }

        // Insert into display_items table
        $displaySql = "INSERT INTO display_items (table_number, order_number, food_item, quantity) 
                       VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($displaySql);
        $stmt->bind_param("issi", $tableNumber, $orderNumber, $item['name'], $item['quantity']);
        if (!$stmt->execute()) {
            die("Error inserting into display_items: " . $stmt->error);
        }
    }

    // Clear the cart for the table
    $clearCartSql = "DELETE FROM cart WHERE table_number = ?";
    $stmt = $conn->prepare($clearCartSql);
    $stmt->bind_param("i", $tableNumber);
    if (!$stmt->execute()) {
        die("Error clearing cart: " . $stmt->error);
    }

    echo "<script>alert('Order Placed Successfully!');</script>";
    header("Location: cart.php?table_number=$tableNumber");
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-light">
    <div class="container mt-5 p-4 bg-white rounded shadow">
        <h1 class="mb-4">Cart - Table <?php echo $tableNumber; ?></h1>

        <!-- Cart Items -->
        <h2>Items in Cart</h2>
        <?php if (!empty($cartItems)): ?>
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                        <tr id="cart-item-<?php echo $item['id']; ?>">
                            <td><?php echo $item['name']; ?></td>
                            <td>
                                <input 
                                    type="number" 
                                    class="form-control quantity-input" 
                                    data-id="<?php echo $item['id']; ?>" 
                                    value="<?php echo $item['quantity']; ?>" 
                                    min="1">
                            </td>
                            <td>₹<?php echo $item['price']; ?></td>
                            <td>₹<span class="item-total"><?php echo $item['quantity'] * $item['price']; ?></span></td>
                            <td>
                                <button 
                                    class="btn btn-danger btn-sm remove-item" 
                                    data-id="<?php echo $item['id']; ?>">Remove</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>

        <!-- Place Order -->
        <form method="POST">
            <button type="submit" name="place_order" class="btn btn-success">Place Order</button>
        </form>

        <!-- Orders Placed -->
        <?php if (!empty($ordersPlaced)): ?>
            <h2 class="mt-4">Order ID: <?php echo $orderNumber; ?></h2>
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ordersPlaced as $order): ?>
                        <tr>
                            <td><?php echo $order['food_item']; ?></td>
                            <td><?php echo $order['quantity']; ?></td>
                            <td>₹<?php echo $order['price']; ?></td>
                            <td>₹<?php echo $order['quantity'] * $order['price']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No orders placed yet.</p>
        <?php endif; ?>

        <!-- Generate Bill -->
        <a href="cart.php?table_number=<?php echo $tableNumber; ?>&generate_bill=true" class="btn btn-primary">Generate Bill</a>

        <!-- Go to Menu -->
        <a href="menu.php" class="btn btn-secondary">Go to Menu</a>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

    </div>

    <script>
        $(document).ready(function() {
            // Handle quantity change
            $('.quantity-input').on('change', function() {
                const id = $(this).data('id');
                const quantity = $(this).val();

                if (quantity <= 0) {
                    alert('Quantity must be greater than zero.');
                    return;
                }

                $.ajax({
                    url: 'update_cart.php',
                    type: 'POST',
                    data: { id: id, quantity: quantity },
                    success: function(response) {
                        alert(response);
                        // Recalculate the total price for the item
                        const price = parseFloat($(`#cart-item-${id}`).find('td:nth-child(3)').text().replace('₹', ''));
                        const total = price * quantity;
                        $(`#cart-item-${id}`).find('.item-total').text(total.toFixed(2));
                    },
                    error: function() {
                        alert('Failed to update the cart.');
                    }
                });
            });

            // Handle remove item
            $('.remove-item').on('click', function() {
                const id = $(this).data('id');

                $.ajax({
                    url: 'remove_cart_item.php',
                    type: 'POST',
                    data: { id: id },
                    success: function(response) {
                        alert(response);
                        // Remove the item row from the table
                        $(`#cart-item-${id}`).remove();
                    },
                    error: function() {
                        alert('Failed to remove the item.');
                    }
                });
            });
        });
    </script>
</body>
</html>
