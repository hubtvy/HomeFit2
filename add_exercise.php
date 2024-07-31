<?php
session_start(); // Start session to store user data

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "homefit";

header('Content-Type: application/json');

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error); // Log connection error
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("Unauthorized access. Please log in."); // Log unauthorized access
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Please log in.']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = $_SESSION['user_id'];
    $workoutName = isset($_POST['workout']) ? trim($_POST['workout']) : '';
    $workoutLink = isset($_POST['link']) ? trim($_POST['link']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';

    // Validate input
    if (empty($workoutName) || empty($workoutLink) || empty($category) || empty($description)) {
        error_log("Validation failed: All fields are required."); // Log validation error
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }

    // Check if the exercise already exists for this user
    $check_sql = "SELECT * FROM user_exercise WHERE user_id = ? AND workout_name = ?";
    $check_stmt = $conn->prepare($check_sql);
    if ($check_stmt === false) {
        error_log('Prepare failed for check statement: ' . $conn->error); // Log prepare error
        echo json_encode(['success' => false, 'message' => 'Prepare statement failed']);
        exit;
    }
    $check_stmt->bind_param('is', $userId, $workoutName);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        error_log("Duplicate entry: You have already added this exercise."); // Log duplicate entry
        echo json_encode(['success' => false, 'message' => 'You have already added this exercise']);
        $check_stmt->close();
        exit;
    }
    $check_stmt->close();

    // Prepare and bind
    $stmt = $conn->prepare('INSERT INTO user_exercise (user_id, workout_name, workout_page_link, category, description, date_added) VALUES (?, ?, ?, ?, ?, NOW())');
    if ($stmt === false) {
        error_log('Prepare failed for insert statement: ' . $conn->error); // Log prepare error
        echo json_encode(['success' => false, 'message' => 'Prepare statement failed']);
        exit;
    }

    $stmt->bind_param('issss', $userId, $workoutName, $workoutLink, $category, $description);

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Exercise added successfully']);
    } else {
        error_log('Execute failed: ' . $stmt->error); // Log execute error
        echo json_encode(['success' => false, 'message' => 'Execute statement failed: ' . $stmt->error]);
    }

    // Debugging: Log the values
    error_log("User ID: " . $userId);
    error_log("Workout Name: " . $workoutName);
    error_log("Workout Link: " . $workoutLink);
    error_log("Category: " . $category);
    error_log("Description: " . $description);

    // Close statement and connection
    $stmt->close();
}

$conn->close();
?>
