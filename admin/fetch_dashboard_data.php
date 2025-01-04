<?php
require"../db.php"; 

$currentDate = date('Y-m-d');

// Total Orders Today
$stmt = $conn->prepare("SELECT COUNT(*) AS total_orders FROM ( SELECT DISTINCT order_number FROM order_details WHERE DATE(created_at) = ?) AS distinct_orders;");
$stmt->bind_param("s", $currentDate);
$stmt->execute();
$stmt->bind_result($totalOrdersToday);
$stmt->fetch();
$stmt->close();

// Total Unpaid Bills
$stmt = $conn->prepare("SELECT COUNT(*) as unpaid_bills FROM bill WHERE payment_status = 'Unpaid'");
$stmt->execute();
$stmt->bind_result($totalUnpaidBills);
$stmt->fetch();
$stmt->close();

// Total Sales
$stmt = $conn->prepare("SELECT SUM(total_bill_amount) as total_sales FROM bill WHERE payment_status = 'Paid'");
$stmt->execute();
$stmt->bind_result($totalSales);
$stmt->fetch();
$stmt->close();

$conn->close();

echo json_encode([
    'totalOrdersToday' => $totalOrdersToday,
    'totalUnpaidBills' => $totalUnpaidBills,
    'totalSales' => number_format($totalSales, 2),
]);
