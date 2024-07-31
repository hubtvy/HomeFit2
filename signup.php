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

session_start();

// Ensure all form data is properly set
$username = isset($_POST['username']) ? $_POST['username'] : '';
$gender = isset($_POST['gender']) ? $_POST['gender'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$year_of_birth = isset($_POST['year_of_birth']) ? $_POST['year_of_birth'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$profile_image_base64 = isset($_POST['profile_image_base64']) ? $_POST['profile_image_base64'] : '';

$errors = [];

// Validate form fields
if (empty($username)) {
    $errors['username'] = "Username is required.";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Invalid email format.";
}

// Check if username already exists
$sql_check_username = "SELECT * FROM users WHERE username = ?";
$stmt_check_username = $conn->prepare($sql_check_username);
$stmt_check_username->bind_param("s", $username);
$stmt_check_username->execute();
$result_check_username = $stmt_check_username->get_result();
if ($result_check_username->num_rows > 0) {
    $errors['username'] = 'Username already exists';
}

// Check if email already exists
$sql_check_email = "SELECT * FROM users WHERE email = ?";
$stmt_check_email = $conn->prepare($sql_check_email);
$stmt_check_email->bind_param("s", $email);
$stmt_check_email->execute();
$result_check_email = $stmt_check_email->get_result();
if ($result_check_email->num_rows > 0) {
    $errors['email'] = 'Email already exists';
}

$stmt_check_username->close();
$stmt_check_email->close();

// Check for other validation errors
if (!empty($errors)) {
    $response = ['status' => 'error', 'errors' => $errors];
    echo json_encode($response);
    exit;
}

// Save profile image if provided
if (!empty($profile_image_base64)) {
    $image_parts = explode(";base64,", $profile_image_base64);
    if (count($image_parts) == 2) {
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        $file_name = uniqid() . '.' . $image_type;
        $file_path = 'profile_images/' . $file_name;
        file_put_contents($file_path, $image_base64);
    } else {
        $response = ['status' => 'error', 'errors' => ['general' => 'Invalid image data.']];
        echo json_encode($response);
        exit;
    }
} else {
    $file_name = null; // No profile image provided
}


// Prepare and bind
$stmt = $conn->prepare("INSERT INTO users (username, gender, email, year_of_birth, password, profile_image) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $username, $gender, $email, $year_of_birth, $password, $file_name);

if ($stmt->execute()) {
    // Retrieve the last inserted user ID
    $user_id = $conn->insert_id;
    
    // Store the user ID in the session
    $_SESSION['user_id'] = $user_id;
    
    // Prepare success response
    $response = ['status' => 'success'];
} else {
    // Prepare error response
    $response = ['status' => 'error', 'message' => $stmt->error];
}

// Close the prepared statement
$stmt->close();

// Close the database connection
$conn->close();

// Send the JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
