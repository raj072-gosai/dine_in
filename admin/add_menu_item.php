<?php
require"../db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $name = $_POST['name'];
    $price = $_POST['price'];
    $category = $_POST['category'];

    // Check if file is uploaded
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        // Get the binary data of the uploaded file
        $photo = $_FILES['photo']['tmp_name'];
        $photoBlob = file_get_contents($photo);

        // Prepare and execute the database query
        $stmt = $conn->prepare("INSERT INTO menu (name, price, category, photo_blob) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdss", $name, $price, $category, $photoBlob);

        // Execute query and provide success or error message
        if ($stmt->execute()) {
            $message = "Menu item added successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }
    } else {
        $message = "Error: Please upload a valid photo.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Menu Item</title>
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
            overflow: hidden;
            position: relative;
        }

        .container:before {
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
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, textarea, select, button {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .back-btn {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            font-size: 16px;
        }
        .back-btn:hover {
            background-color: #0056b3;
        }
        .message {
            text-align: center;
            font-weight: bold;
            color: green;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add Menu Item</h1>
        <?php if (isset($message)): ?>
            <p class="message <?php echo strpos($message, 'Error') !== false ? 'error' : ''; ?>">
                <?php echo $message; ?>
            </p>
        <?php endif; ?>
        <form action="add_menu_item.php" method="POST" enctype="multipart/form-data">
            <label for="name">Item Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="price">Price:</label>
            <input type="number" id="price" name="price" step="0.01" required>

            <label for="category">Category:</label>
            <select id="category" name="category" required>
                <option value="Pizza">Pizza</option>
                <option value="Salad">Salad</option>
                <option value="Main Course">Main Course</option>
                <option value="Dessert">Dessert</option>
            </select>

            <label for="photo">Photo:</label>
            <input type="file" id="photo" name="photo" accept="image/*" required>

            <button type="submit">Add Item</button>
            <a href="admin_dashboard.php" class="back-btn">Back to Dashboard</a>
        </form>
    </div>
</body>
</html>
