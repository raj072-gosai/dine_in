<?php
session_start();
require"../db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderNumber = $_POST['order_number'];
    $paymentDate = date('Y-m-d H:i:s');

    // Update payment status
    $stmt = $conn->prepare("UPDATE bill SET payment_status = 'Paid', updated_at = ? WHERE order_number = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => $conn->error]);
        exit;
    }
    $stmt->bind_param("ss", $paymentDate, $orderNumber);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => "Order #$orderNumber marked as paid."]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update payment status.']);
    }
    $stmt->close();
    $conn->close();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch unpaid bills
    $stmt = $conn->prepare("SELECT order_number, username, contact_number, total_bill_amount, created_at FROM bill WHERE payment_status = 'Unpaid' ORDER BY created_at DESC");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => $conn->error]);
        exit;
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $unpaidOrders = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->close();

    echo json_encode(['success' => true, 'data' => $unpaidOrders]);
    exit;
}
?>
