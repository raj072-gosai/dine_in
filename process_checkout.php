<?php
require"db.php"; // Include the database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $contactNumber = $_POST['contact_number'];
    $orderNumber = $_POST['order_number'];
    $totalBillAmount = $_POST['total_bill_amount'];
    $paymentMode = $_POST['payment_mode'];
    $paymentStatus = 'Unpaid';

    $sql = "INSERT INTO users (username, contact_number, order_number, total_bill_amount, payment_mode, payment_status)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssds", $username, $contactNumber, $orderNumber, $totalBillAmount, $paymentMode, $paymentStatus);

    if ($stmt->execute()) {
        echo "User details saved successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
