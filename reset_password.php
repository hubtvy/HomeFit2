<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "homefit";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start(); // Ensure session is started to access session variables

$response = "";
$user_id = $_GET['user_id'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $user_id = $_POST['user_id'];

    if ($new_password !== $confirm_password) {
        $response = json_encode(['status' => 'error', 'message' => 'Passwords do not match.']);
    } else {
        // Fetch the current password from the database
        $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($current_password);
        $stmt->fetch();
        $stmt->close();

        if ($new_password === $current_password) {
            $response = json_encode(['status' => 'error', 'message' => 'Cannot reset to the same password.']);
        } else {
            // Update password in the database
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $stmt->bind_param("si", $new_password, $user_id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $response = json_encode(['status' => 'success', 'message' => 'Password has been reset.']);
            } else {
                $response = json_encode(['status' => 'error', 'message' => 'Failed to reset password.']);
            }

            $stmt->close();
        }
    }

    $conn->close();
    echo $response;
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #EEDBFF;
            margin: 0;
            padding: 0;
        }
        .logo {
            position: absolute;
            top: 10px;
            left: 20px;
        }
        .logo img {
            max-height: 50px;
        }
        .container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 15px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border: none;
            text-align: center;
            margin-top: 150px;
        }
        h2 {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 30px;
            color: #333;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-control {
            border-radius: 10px;
            background-color: #f1f1f1;
            border: 1px solid #ddd;
        }
        .btn-primary {
            border-radius: 10px;
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .back-to-login {
            text-align: center;
            color: #007bff;
            font-size: 16px;
            margin-top: 10px;
        }
        .back-to-login a {
            color: #007bff;
            text-decoration: none;
        }
        .back-to-login a:hover {
            color: #0056b3;
        }
        .alert {
            display: none;
        }
    </style>
</head>
<body>
<div class="logo">
        <img src="Images/logo.png" alt="HomeFit">
    </div>
    <div class="container">
        <h2>Reset Password</h2>
        <form id="resetPasswordForm" method="post">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
            <div class="form-group">
                <input type="password" class="form-control" id="new_password" name="new_password" placeholder="New Password" required>
            </div>
            <div class="form-group">
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
            </div>
            <button type="submit" class="btn btn-primary">Reset Password</button>
        </form>
        <div class="back-to-login"><a href="index.html">Back To Log in</a></div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#resetPasswordForm').on('submit', function(event) {
                event.preventDefault(); // Prevent the default form submission

                var formData = $(this).serialize();

                $.ajax({
                    url: '',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        alert(response.message);
                        if (response.status === 'success') {
                            window.location.href = 'index.html';
                        }
                    },
                    error: function() {
                        alert('An error occurred. Please try again.');
                    }
                });
            });
        });
    </script>
</body>
</html>
