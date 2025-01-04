<?php
require"../db.php";

if (isset($_GET['table_number'])) {
    $tableNumber = intval($_GET['table_number']);
    $deleteSql = "DELETE FROM display_items WHERE table_number = ?";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bind_param("i", $tableNumber);

    if (!$stmt->execute()) {
        echo json_encode(['error' => $stmt->error]);
    } else {
        echo json_encode(['success' => true]);
    }
}

$conn->close();
?>
