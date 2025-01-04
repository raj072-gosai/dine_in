<?php
// Hardcoded admin credentials
$adminUsername = "admin";
$adminPassword = "pass1234";

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate credentials
    if ($username === $adminUsername && $password === $adminPassword) {
        session_start();
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin_dashboard.php');
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kaushan+Script&family=Poppins&display=swap');

        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(to top, #fff 10%, rgba(93, 42, 141, 0.4) 90%) no-repeat;
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .wrapper {
            max-width: 500px;
            width: 100%;
            padding: 30px 40px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.1);
        }

        .h2 {
            font-family: 'Kaushan Script', cursive;
            font-size: 3.5rem;
            font-weight: bold;
            color: #400485;
            font-style: italic;
            text-align: center;
        }

        .h4 {
            font-family: 'Poppins', sans-serif;
            color: #6c757d;
            text-align: center;
            margin-top: 10px;
        }

        .input-field {
            display: flex;
            align-items: center;
            padding: 5px;
            border-radius: 5px;
            border: 1px solid #400485;
            color: #400485;
            margin-top: 15px;
        }

        .input-field:hover {
            border-color: #7b4ca0;
            color: #7b4ca0;
        }

        input {
            flex: 1;
            border: none;
            outline: none;
            padding: 5px;
            font-family: 'Poppins', sans-serif;
        }

        .btn {
            display: block;
            width: 100%;
            border-radius: 20px;
            background-color: #400485;
            color: #fff;
            padding: 10px 0;
            text-align: center;
            margin-top: 20px;
            font-size: 1rem;
            font-weight: bold;
            transition: background 0.3s ease, box-shadow 0.3s ease;
        }

        .btn:hover {
            background-color: #55268b;
            box-shadow: 0 8px 15px rgba(93, 42, 141, 0.4);
        }

        .btn:active {
            background-color: #33115e;
            box-shadow: inset 0 3px 6px rgba(0, 0, 0, 0.2);
        }

        .error {
            color: #ff6b6b;
            font-weight: bold;
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="h2">Creativity</div>
        <div class="h4">Enter Admin login details</div>
        <form method="POST">
            <div class="input-field">
                <span class="far fa-user p-2"></span>
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="input-field">
                <span class="fas fa-lock p-2"></span>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn">Log in</button>
        </form>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
    </div>
</body>

</html>
