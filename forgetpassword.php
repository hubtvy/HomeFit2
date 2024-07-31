<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "homefit";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];

    // Check if username exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $username_result = $stmt->get_result();

    if ($username_result->num_rows == 0) {
        // Username not found
        echo json_encode(['success' => false, 'message' => 'Username not found.']);
        $stmt->close();
        $conn->close();
        exit;
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $email_result = $stmt->get_result();

    if ($email_result->num_rows == 0) {
        // Email not found
        echo json_encode(['success' => false, 'message' => 'Email not found.']);
        $stmt->close();
        $conn->close();
        exit;
    }

    // Check if both username and email match
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // User found
        $user = $result->fetch_assoc();
        $user_id = $user['user_id'];
        echo json_encode(['success' => true, 'user_id' => $user_id]);
    } else {
        // Username and email do not match
        echo json_encode(['success' => false, 'message' => 'Username and email do not match.']);
    }

    $stmt->close();
    $conn->close();
}
?>
