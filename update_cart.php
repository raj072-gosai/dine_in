<?php
require"db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : null;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : null;

    if (!$id || $quantity <= 0) {
        echo "Invalid quantity or cart item.";
        exit;
    }

    $sql = "UPDATE cart SET quantity = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $quantity, $id);

    if ($stmt->execute()) {
        echo "Cart updated successfully.";
    } else {
        echo "Failed to update cart: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}

$conn->close();
?>
