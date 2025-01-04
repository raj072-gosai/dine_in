<?php
session_start(); // Start session

// Include database connection
require"db.php";

// Check for POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $menuId = isset($_POST['menu_id']) ? intval($_POST['menu_id']) : null;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    // Validate session for table number
    if (isset($_SESSION['table_number'])) {
        $tableNumber = $_SESSION['table_number'];
    } else {
        echo "Table number not set in session.";
        exit;
    }

    if (!$menuId || $quantity <= 0) {
        echo "Invalid input. Please ensure all fields are valid.";
        exit;
    }

    // Check if item already exists in the cart
    $sqlCheck = "SELECT id, quantity FROM cart WHERE menu_id = ? AND table_number = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param("ii", $menuId, $tableNumber);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows > 0) {
        // Update quantity
        $row = $resultCheck->fetch_assoc();
        $newQuantity = $row['quantity'] + $quantity;

        $sqlUpdate = "UPDATE cart SET quantity = ? WHERE id = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("ii", $newQuantity, $row['id']);
        if ($stmtUpdate->execute()) {
            echo "Cart updated successfully.";
        } else {
            echo "Failed to update cart.";
        }
    } else {
        // Insert new item
        $sqlInsert = "INSERT INTO cart (table_number, menu_id, quantity) VALUES (?, ?, ?)";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->bind_param("iii", $tableNumber, $menuId, $quantity);
        if ($stmtInsert->execute()) {
            echo "Item added to cart.";
        } else {
            echo "Failed to add item to cart.";
        }
    }

    $stmtCheck->close();
} else {
    echo "Invalid request method.";
}

$conn->close();
?>
