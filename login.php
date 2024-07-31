<?php
session_start(); // Start the session

$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "homefit";

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form data is set
if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and bind
    $stmt = $conn->prepare("SELECT user_id, username, gender, email, year_of_birth, profile_image FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);

    // Execute the statement
    $stmt->execute();
    $stmt->store_result();

    // Check if the user exists
    if ($stmt->num_rows > 0) {
        // Bind result variables
        $stmt->bind_result($id, $username, $gender, $email, $year_of_birth, $profile_image);
        $stmt->fetch();

        // Store user data in session
        $_SESSION['user_id'] = $id;
        $_SESSION['username'] = $username;
        $_SESSION['gender'] = $gender;
        $_SESSION['email'] = $email;
        $_SESSION['year_of_birth'] = $year_of_birth;
        $_SESSION['profile_image'] = $profile_image;

        // Redirect to the home page
        header("Location: home.html");
        exit();
    } else {
        // Invalid credentials
        $error_message = urlencode("Invalid username or password.");
        header("Location: index.html?error=" . $error_message);
        exit();
    }

    $stmt->close();
} else {
    // Form data not set
    $error_message = urlencode("Please enter username and password.");
    header("Location: index.html?error=" . $error_message);
    exit();
}

$conn->close();
?>
