<?php
session_start(); // Start session
require "db.php";

// Check if table number is set in the query string or session
if (isset($_GET['table_number'])) {
    $tableNumber = intval($_GET['table_number']);
    $_SESSION['table_number'] = $tableNumber; // Save table number to session
} elseif (isset($_SESSION['table_number'])) {
    $tableNumber = $_SESSION['table_number']; // Retrieve table number from session
} else {
    die("Table number is required.");
}

// Fetch categories
$categorySql = "SELECT DISTINCT category FROM menu";
$categoryResult = $conn->query($categorySql);
$categories = $categoryResult && $categoryResult->num_rows > 0 ? $categoryResult->fetch_all(MYSQLI_ASSOC) : [];

// Fetch menu items
$menuSql = "SELECT * FROM menu";
$menuResult = $conn->query($menuSql);
$menuItems = $menuResult && $menuResult->num_rows > 0 ? $menuResult->fetch_all(MYSQLI_ASSOC) : [];

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .menu-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .menu-item img {
            width: 100px;
            height: 100px;
            margin-right: 15px;
            border-radius: 5px;
            object-fit: cover;
        }
        .menu-item-info {
            flex-grow: 1;
            margin-right: 20px;
        }
        .menu-item-info h3 {
            margin: 0 0 10px;
        }
        .menu-item-info p {
            margin: 5px 0;
        }
        .menu-item-controls {
            text-align: center;
        }
        .menu-item-controls input {
            width: 50px;
            margin-bottom: 10px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        .menu-item-controls button {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .menu-item-controls button:hover {
            background-color: #0056b3;
        }
        .cart-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .cart-btn:hover {
            background: #218838;
        }
        .search-bar {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .search-bar input, .search-bar select {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-right: 10px;
        }
        #noResults {
            display: none;
            color: red;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <!-- Cart Button -->
    <button class="cart-btn" onclick="window.location.href='cart.php?table_number=<?php echo $tableNumber; ?>'">
        Go to Cart
    </button>

    <div class="container">
        <h1>Menu</h1>
        <p>Table Number: <?php echo $tableNumber; ?></p>

        <!-- Search and Filter Bar -->
        <div class="search-bar">
            <form method="GET" action="" id="searchForm">
                <input type="hidden" name="table_number" value="<?php echo $tableNumber; ?>">
                <input 
                    type="text" 
                    name="search" 
                    id="searchInput" 
                    placeholder="Search menu items..." 
                    oninput="filterMenu()"
                >
                <select name="category" id="categorySelect" onchange="filterMenu()">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category['category']); ?>">
                            <?php echo htmlspecialchars($category['category']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <!-- Menu Items -->
        <div id="menuContainer">
            <?php if (!empty($menuItems)): ?>
                <?php foreach ($menuItems as $item): ?>
                    <div class="menu-item" data-name="<?php echo htmlspecialchars($item['name']); ?>" data-category="<?php echo htmlspecialchars($item['category']); ?>">
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($item['photo_blob']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <div class="menu-item-info">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p>Price: ₹<?php echo htmlspecialchars($item['price']); ?></p>
                            <p>Category: <?php echo htmlspecialchars($item['category']); ?></p>
                        </div>
                        <div class="menu-item-controls">
                            <input type="number" value="1" min="1" id="quantity-<?php echo $item['id']; ?>">
                            <button onclick="addToCart(<?php echo $item['id']; ?>, document.getElementById('quantity-<?php echo $item['id']; ?>').value)">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <p id="noResults">No menu items found.</p>
    </div>

    <script>
    function filterMenu() {
        const searchInput = document.getElementById("searchInput").value.toLowerCase();
        const categorySelect = document.getElementById("categorySelect").value;
        const menuItems = document.querySelectorAll(".menu-item");
        let found = false;

        menuItems.forEach(item => {
            const name = item.getAttribute("data-name").toLowerCase();
            const category = item.getAttribute("data-category");

            if ((name.includes(searchInput) || searchInput === "") &&
                (category === categorySelect || categorySelect === "")) {
                item.style.display = "flex";
                found = true;
            } else {
                item.style.display = "none";
            }
        });

        document.getElementById("noResults").style.display = found ? "none" : "block";
    }

    function addToCart(menuId, quantity) {
        $.ajax({
            url: "add_to_cart.php",
            type: "POST",
            data: { menu_id: menuId, quantity: quantity },
            success: function(response) {
                alert(response);
            },
            error: function(xhr, status, error) {
                alert("An error occurred: " + error);
            }
        });
    }
    </script>
</body>
</html>
