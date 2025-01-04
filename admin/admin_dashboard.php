<!-- <?php
require"../db.php";

// Initialize variables
$totalOrdersToday = 0;
$totalUnpaidBills = 0;
$totalSales = 0;

$currentDate = date('Y-m-d');

// Total Orders Today (count distinct order numbers from the `orders` table)
$stmt = $conn->prepare("SELECT COUNT(*) AS total_orders FROM ( SELECT DISTINCT order_number FROM order_details WHERE DATE(created_at) = ?) AS distinct_orders;");
if (!$stmt) {
    die("SQL Error: " . $conn->error);
}
$stmt->bind_param("s", $currentDate);
$stmt->execute();
$stmt->bind_result($totalOrdersToday);
$stmt->fetch();
$stmt->close();

// Total Unpaid Bills (count distinct order numbers from the `bill` table with 'Unpaid' status)
$stmt = $conn->prepare("SELECT COUNT(*) as unpaid_bills FROM bill WHERE payment_status = 'Unpaid'");
if (!$stmt) {
    die("SQL Error: " . $conn->error);
}
$stmt->execute();
$stmt->bind_result($totalUnpaidBills);
$stmt->fetch();
$stmt->close();

// Total Sales (sum of total bill amounts from the `bill` table with 'Paid' status)
$stmt = $conn->prepare("SELECT SUM(total_bill_amount) as total_sales FROM bill WHERE payment_status = 'Paid'");
if (!$stmt) {
    die("SQL Error: " . $conn->error);
}
$stmt->execute();
$stmt->bind_result($totalSales);
$stmt->fetch();
$stmt->close();

// Fetch all bills
$bills = [];
$stmt = $conn->prepare("SELECT order_number, username, contact_number, total_bill_amount, payment_status, created_at FROM bill ORDER BY created_at DESC");
if (!$stmt) {
    die("SQL Error: " . $conn->error);
}
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $bills[] = $row;
}
$stmt->close();

$conn->close();
?> -->

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #e3f2fd, #bbdefb, #90caf9);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            position: relative;
        }

        @keyframes gradientBG {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .dashboard {
            max-width: 1200px;
            margin: 40px auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
        }

        .dashboard:before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.8), rgba(0, 0, 0, 0));
            animation: rotate 20s linear infinite;
            z-index: -1;
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }


        .dashboard {

            max-width: 1200px;
            margin: 20px auto;
            padding: 30px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .dashboard h1 {
            text-align: center;
            font-size: 2.5rem;
            color: #007bff;
            margin-bottom: 20px;
        }

        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .card {
            flex: 1;
            min-width: 280px;
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: #fff;
            padding: 30px 20px;
            text-align: center;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.2);
        }

        .card h2 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .card p {
            font-size: 1.25rem;
            margin: 0;
            font-weight: bold;
        }

        .actions {
            margin-top: 30px;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }

        .actions a {
            text-decoration: none;
            background-color: #28a745;
            color: white;
            padding: 12px 25px;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: bold;
            transition: background 0.3s ease, transform 0.3s ease;
        }

        .actions a:hover {
            background-color: #218838;
            transform: scale(1.05);
        }

        .logout {
            text-align: center;
            margin-top: 30px;
        }

        .logout a {
            color: #007bff;
            text-decoration: none;
            font-size: 1rem;
            transition: color 0.3s ease;
        }

        .logout a:hover {
            color: #0056b3;
        }

        .table-container {
            margin-top: 40px;
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        table th,
        table td {
            padding: 15px;
            text-align: center;
            border: 1px solid #ddd;
            font-size: 0.95rem;
        }

        table th {
            background-color: #007bff;
            color: white;
            text-transform: uppercase;
        }

        table tr:nth-child(even) {
            background-color: #f4f4f4;
        }

        table tr:hover {
            background-color: #e9ecef;
        }

        @media(max-width: 768px) {
            .card-container {
                flex-direction: column;
                align-items: center;
            }

            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard">
        <h1>Admin Dashboard</h1>
        <div class="card-container">
            <div class="card">
                <h2>Total Orders Today</h2>
                <p><?php echo $totalOrdersToday; ?></p>
            </div>
            <div class="card">
                <h2>Total Unpaid Bills</h2>
                <p><?php echo $totalUnpaidBills; ?></p>
            </div>
            <div class="card">
                <h2>Total Sales</h2>
                <p>₹<?php echo number_format($totalSales, 2); ?></p>
            </div>
        </div>
        <div class="actions">
            <a href="add_menu_item.php">Add Menu Item</a>
            <a href="update_payment_status.php">Update Payment Status</a>
        </div>
        <div class="logout">
            <a href="admin_logout.php">Logout</a>
        </div>
    </div>
    <script>
        function fetchData() {
            const xhr = new XMLHttpRequest();
            xhr.open("GET", "fetch_dashboard_data.php", true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    const data = JSON.parse(xhr.responseText);
                    document.querySelector(".card:nth-child(1) p").innerText = data.totalOrdersToday;
                    document.querySelector(".card:nth-child(2) p").innerText = data.totalUnpaidBills;
                    document.querySelector(".card:nth-child(3) p").innerText = "₹" + data.totalSales;
                } else {
                    console.error("Failed to fetch data");
                }
            };
            xhr.send();
        }

        // Fetch data every 10 seconds
        setInterval(fetchData, 3000);
        fetchData(); // Initial fetch
    </script>

</body>

</html>