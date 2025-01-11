<?php
require "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $quantity = intval($_POST['quantity']);

    if ($id > 0 && $quantity > 0) {
        $updateSql = "UPDATE cart SET quantity = ? WHERE id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("ii", $quantity, $id);

        if ($stmt->execute()) {
            echo "Cart updated successfully.";
        } else {
            echo "Failed to update cart: " . $conn->error;
        }
    } else {
        echo "Invalid data provided.";
    }
}
?>
