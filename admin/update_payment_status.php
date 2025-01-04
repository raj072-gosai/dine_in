<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Payment Status</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #e3f2fd, #bbdefb, #90caf9);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            margin: 0;
            padding: 0;
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

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
            font-size: 16px;
        }

        th {
            background-color: #007bff;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
        }

        td {
            border-bottom: 1px solid #ddd;
        }

        td:last-child {
            text-align: center;
        }

        .btn {
            background-color: #28a745;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #218838;
        }

        .back {
            text-align: center;
            margin-top: 20px;
        }

        .back a {
            text-decoration: none;
            background-color: #007bff;
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 5px;
        }

        .back a:hover {
            background-color: #0056b3;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function markAsPaid(orderNumber) {
            $.ajax({
                url: 'ajax_handler.php',
                type: 'POST',
                data: { order_number: orderNumber },
                success: function (response) {
                    const res = JSON.parse(response);
                    if (res.success) {
                        alert(res.message);
                        fetchUnpaidOrders();
                    } else {
                        alert('Error: ' + res.message);
                    }
                },
                error: function () {
                    alert('An error occurred while updating payment status.');
                }
            });
        }

        function fetchUnpaidOrders() {
            $.ajax({
                url: 'ajax_handler.php',
                type: 'GET',
                success: function (response) {
                    const res = JSON.parse(response);
                    if (res.success) {
                        const orders = res.data;
                        const tbody = $('table tbody');
                        tbody.empty();
                        if (orders.length > 0) {
                            orders.forEach(order => {
                                const row = `
                                    <tr>
                                        <td>${order.order_number}</td>
                                        <td>${order.username}</td>
                                        <td>${order.contact_number}</td>
                                        <td>₹${parseFloat(order.total_bill_amount).toFixed(2)}</td>
                                        <td>${order.created_at}</td>
                                        <td>
                                            <button class="btn" onclick="markAsPaid('${order.order_number}')">Mark as Paid</button>
                                        </td>
                                    </tr>
                                `;
                                tbody.append(row);
                            });
                        } else {
                            tbody.append('<tr><td colspan="6" style="text-align: center;">No unpaid bills found.</td></tr>');
                        }
                    } else {
                        alert('Error: ' + res.message);
                    }
                },
                error: function () {
                    alert('An error occurred while fetching unpaid orders.');
                }
            });
        }

        $(document).ready(function () {
            fetchUnpaidOrders();
            // Reload data every 5 seconds
            setInterval(fetchUnpaidOrders, 5000);
        });
    </script>
</head>

<body>
    <div class="container">
        <h1>Update Payment Status</h1>
        <table>
            <thead>
                <tr>
                    <th>Order Number</th>
                    <th>Name</th>
                    <th>Contact Number</th>
                    <th>Total Amount (₹)</th>
                    <th>Date Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="6" style="text-align: center;">Loading...</td>
                </tr>
            </tbody>
        </table>

        <div class="back">
            <a href="admin_dashboard.php">Back to Dashboard</a>
        </div>
    </div>
</body>

</html>