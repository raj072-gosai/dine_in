<?php
session_start();
require "db.php";

$tableNumber = isset($_GET['table_number']) ? intval($_GET['table_number']) : null;
if (!$tableNumber) {
    die("Table number is required.");
}

// Fetch cart items
$cartSql = "SELECT c.id, c.menu_id, c.quantity, m.name, m.price 
            FROM cart c
            INNER JOIN menu m ON c.menu_id = m.id
            WHERE c.table_number = ?";
$stmt = $conn->prepare($cartSql);
$stmt->bind_param("i", $tableNumber);
$stmt->execute();
$cartItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

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
