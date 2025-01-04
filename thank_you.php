<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #e3f2fd, #bbdefb, #90caf9);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            margin: 0;
            padding: 0;
            display: flex; /* Enable Flexbox */
            justify-content: center; /* Center horizontally */
            align-items: center; /* Center vertically */
            height: 100vh; /* Full viewport height */
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
            max-width: 600px;
            padding: 30px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center; /* Center text */
        }

        h1 {
            color: #28a745;
            font-size: 36px;
            margin-bottom: 10px;
        }
        p {
            color: #333;
            font-size: 18px;
            margin: 15px 0;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
            color: #ffffff;
            background: #007bff;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s ease;
        }
        a:hover {
            background: #0056b3;
        }
    </style>
    <script>
        // Disable back navigation
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            history.pushState(null, null, location.href);
        };

        // Optional: Close the browser tab after 10 seconds
        setTimeout(() => {
            try {
                window.close();
            } catch (e) {
                console.log("Tab close is blocked. Redirecting instead.");
                window.location.href = "about:blank"; // Redirect to a blank page
            }
        }, 10000);
    </script>
</head>
<body>
    <div class="dashboard">
        <h1>Thank You for Dining with Us!</h1>
        <p>We hope you had a great experience. Your feedback is valuable to us!</p>
        <p>Hope to see you again soon!</p>
    </div>
</body>
</html>
