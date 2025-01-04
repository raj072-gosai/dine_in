<?php
require"../db.php";

// Fetch grouped orders by table number
$fetchSql = "SELECT table_number, GROUP_CONCAT(CONCAT(food_item, ' (', quantity, ')') SEPARATOR ', ') as orders 
             FROM display_items GROUP BY table_number ORDER BY table_number";
$result = $conn->query($fetchSql);
$rows = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($rows);
$conn->close();
?>
