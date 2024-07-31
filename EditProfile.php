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

if (!isset($_SESSION['user_id'])) {
    // Redirect to login or handle unauthorized access
    header("Location: index.html"); // Redirect to your login page
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$sql = "SELECT username, gender, email, year_of_birth, profile_image FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    die("User not found.");
}

// Fetch survey data
$sql_survey = "SELECT age, weight, height, fitness_goal, health, gym FROM survey WHERE user_id = ?";
$stmt_survey = $conn->prepare($sql_survey);
if ($stmt_survey === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt_survey->bind_param("i", $user_id);
$stmt_survey->execute();
$result_survey = $stmt_survey->get_result();

if ($result_survey->num_rows > 0) {
    $survey = $result_survey->fetch_assoc();
} else {
    $survey = [
        'age' => '',
        'weight' => '',
        'height' => '',
        'fitness_goal' => '',
        'health' => '',
        'gym' => ''
    ];
}

// Handle form submission for profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = $_POST['update_username'] ?? '';
    $new_gender = $_POST['update_gender'] ?? '';
    $new_email = $_POST['update_email'] ?? '';
    $new_year_of_birth = $_POST['update_year_of_birth'] ?? '';
    $new_age = $_POST['update_age'] ?? '';
    $new_weight = $_POST['update_weight'] ?? '';
    $new_height = $_POST['update_height'] ?? '';
    $new_fitness_goal = $_POST['update_fitness_goal'] ?? '';
    $new_health = $_POST['update_health'] ?? '';
    $new_gym = $_POST['update_gym'] ?? '';

    // Process profile image upload
    if (!empty($_FILES['update_profile_image']['name'])) {
        $new_profile_image = $_FILES['update_profile_image']['name'];
        $target_dir = "profile_images/";
        $target_file = $target_dir . basename($new_profile_image);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if the file is an actual image
        $check = getimagesize($_FILES['update_profile_image']['tmp_name']);
        if ($check === false) {
            die("File is not an image.");
        }

        // Check file size
        if ($_FILES['update_profile_image']['size'] > 500000) {
            die("Sorry, your file is too large.");
        }

        // Allow certain file formats
        if (!in_array($imageFileType, ["jpg", "jpeg", "png"])) {
            die("Sorry, only JPG, JPEG, and PNG files are allowed.");
        }

        // Move uploaded file to target directory
        if (!move_uploaded_file($_FILES['update_profile_image']['tmp_name'], $target_file)) {
            die("Sorry, there was an error uploading your file.");
        }

        // Update profile image in database
        $sql = "UPDATE users SET profile_image = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("si", $new_profile_image, $user_id);
        if (!$stmt->execute()) {
            die("Query execution failed: " . $stmt->error);
        }
    }

    // Update other user details in database
    $sql = "UPDATE users SET username = ?, gender = ?, email = ?, year_of_birth = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ssssi", $new_username, $new_gender, $new_email, $new_year_of_birth, $user_id);
    if (!$stmt->execute()) {
        die("Query execution failed: " . $stmt->error);
    }

    // Update survey details in database
    $sql_survey_update = "UPDATE survey SET age = ?, weight = ?, height = ?, fitness_goal = ?, health = ?, gym = ? WHERE user_id = ?";
    $stmt_survey_update = $conn->prepare($sql_survey_update);
    if ($stmt_survey_update === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt_survey_update->bind_param("ssssssi", $new_age, $new_weight, $new_height, $new_fitness_goal, $new_health, $new_gym, $user_id);
    if (!$stmt_survey_update->execute()) {
        die("Query execution failed: " . $stmt_survey_update->error);
    }

    // Redirect to profile page to show updated info
    header("Location: Profile.html");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
    /* Place your CSS here */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        list-style: none;
        text-decoration: none;
    }

    body {
        font-family: Arial, sans-serif;
        background-color: #EEDBFF;
        position: relative;
        min-height: 100%;
        width: 100%;
    }

    .sidebar {
        width: 78px;
        height: 100%;
        background: #0c0c0c;
        padding: 6px 14px;
        top: 0;
        left: 0;
        position: fixed;
        transition: all 0.5s ease;
    }

    .sidebar.active {
        width: 240px;
    }

    .sidebar .logo {
        position: absolute;
        top: 10px;
        left: 20px;
        display: flex;
        width: 100%;
        align-items: center;
        opacity: 0;
        pointer-events: none;
        transition: all 0.5s ease;
    }

    .sidebar .logo img {
        max-height: 40px;
    }

    .sidebar .logo .logo_name {
        font-size: 20px;
        font-weight: bold;
        color: #fff;
        margin-left: 10px;
    }

    .sidebar.active .logo {
        opacity: 1;
        pointer-events: auto;
    }

    .sidebar #btn {
        color: #fff;
        position: absolute;
        left: 56%;
        top: 6px;
        font-size: 20px;
        height: 50px;
        width: 50px;
        text-align: center;
        line-height: 50px;
        transform: translateX(-50%);
        cursor: pointer;
    }

    .sidebar.active #btn {
        left: 90%;
    }

    .sidebar ul {
        margin-top: 55px;
    }

    .sidebar ul li {
        position: relative;
        height: 55px;
        width: 100%;
        list-style: none;
        margin: 10px 5px;
        line-height: 50px;
    }

    .sidebar ul li .tooltip {
        position: absolute;
        left: 122px;
        top: 50%;
        transform: translate(-50%, -50%);
        border-radius: 6px;
        text-align: center;
        height: 35px;
        width: 122px;
        background: #fff;
        box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
        transition: 0s;
        opacity: 0;
        pointer-events: none;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .sidebar ul li:hover .tooltip {
        transition: all 0.5s ease;
        opacity: 1;
        top: 50%;
    }

    .sidebar.active ul li .tooltip {
        display: none;
    }

    .sidebar ul li a {
        color: #fff;
        display: flex;
        align-items: center;
        text-decoration: none;
        transition: all 0.4s ease;
        border-radius: 12px;
        white-space: nowrap;
    }

    .sidebar ul li a:hover {
        color: #19181a;
        background: #fff;
    }

    .sidebar ul li a i {
        height: 50px;
        min-width: 50px;
        border-radius: 12px;
        line-height: 50px;
        text-align: center;
    }

    .sidebar ul li a .links_name {
        opacity: 0;
        pointer-events: none;
        transition: all 0.5s ease;
    }

    .sidebar.active ul li a .links_name {
        opacity: 1;
        pointer-events: auto;
    }

    .home_content {
        position: absolute;
        height: 100%;
        width: calc(100% - 78px);
        left: 78px;
        transition: all 0.5s ease;
        overflow: auto;
        min-height: 100vh;
    }

    .home_content .text {
        font-size: 30px;
        font-weight: bold;
        text-align: center;
        color: #1d1b31;
        margin: 12px;
    }

    .sidebar.active ~ .home_content {
        width: calc(100% - 240px);
        left: 240px;
    }

    .container {
        display: flex;
        justify-content: space-between;
        margin: 50px 100px;
    }

    .profile-container, .survey-container {
        border-radius: 10px;
        padding: 20px;
        background-color: #f9f9f9;
        width: 45%;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .survey-container {
        background-color: #DBF3FA;
    }

    .form-container label, .form-container input, .form-container select {
        display: block;
        margin: 10px 0;
    }

    .profile-image-container {
        position: relative;
        text-align: center;
        
    }
    
    #profileImagePreview {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
    }

    .profile-image-container img {
        width:150px;
        height: 150px;
        border-radius: 50%;
    }

    .profile-image-container .add-icon {
        position: absolute;
        bottom: -15px;
        right: 170px;
        font-size: 24px;
        color: #333;
    }

    .profile-container img {
        width: 160px;
        height: 160px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 30px;
        margin-left: 10px;
    }

    form label, form input, form select {
       display: block;
       margin-bottom: 10px; /* Space between form elements */
      
    }

    form input[type="text"],
    form input[type="email"],
    form input[type="password"],
    form input[type="date"],
    form input[type="number"], /* Added this line */
    form select {
        padding: 10px; /* Increase padding for better usability */
        border-radius: 5px;
        border: 1px solid #ccc;
        width: 100%;
        box-sizing: border-box; /* Ensure padding doesn't affect width */
    }

    .add-icon {
        position: absolute; 
        background: #fff;
        border-radius: 50%;
        padding: 0.75em; /* Relative unit for padding */
        cursor: pointer;
        font-size: 1.5em; /* Relative unit for font size */
        z-index: 10;
    }

    .add-icon i {
        color: #333;
    }

    .profile-container h2, .survey-container h2 {
        margin-bottom: 20px;
    }

    .profile-container form, .survey-container form {
        width: 100%;
        max-width: 400px;
    }

    .profile-container form label, .survey-container form label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
    }

    .form-submit {
        display: flex; /* Use flexbox for centering */
        justify-content: center; /* Center horizontally */
        margin-top: 20px; /* Add margin to separate from the form fields */
    }

    .form-submit input[type="submit"] {
        padding: 10px 20px; /* Adjust padding for better button size */
        border: none;
        border-radius: 6px;
        background-color: #4CAF50;
        color: #ffff;
        font-size: 16px;
        cursor: pointer;
    }


    .back-button-container {
        position: relative;
        text-align: left;
        width: 100%;
        margin-top: 20px;
        margin-left: 20px;
        margin-bottom: 20px;
    }

    .back-button-container a {
        text-decoration: none;
        background-color: #f0f0f0;
        color: #000;
        padding: 10px 15px;
        border-radius: 4px;
        transition: all 0.3s ease;
    }

    .back-button-container a:hover {
        background-color: #ddd;
    }

    #weightDisplay, #heightDisplay {
        font-size: 16px;
        font-weight: bold;
    }

    /* Container for labels and checkboxes/radio buttons */
    #update_health, #update_gym {
        width: 100%;
        padding: 0;
        margin: 0;
    }

    /* Flexbox styling for alignment */
    #update_health div, #update_gym div {
        display: flex;
        align-items: left;
        margin-bottom: 5px;
    }

    #update_health input[type="radio"],
    #update_gym input[type="radio"],
    #update_health input[type="radio"],
    #update_gym input[type="radio"] {
        width: 15px;
        height: 15px;
        margin-right: 10px;
    }

    /* Align labels to the left and ensure normal font weight */
    #update_health label {
        margin-right: 0;
        font-weight: normal;
    }

    #update_gym label {
        margin-right: 0;
        font-weight: normal;
    }

    /* Make sure only one option can be selected at a time */
    #update_gym input[type="radio"] {
        margin-right: 16px;
    }

    .slider-container {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }

    .slider-container input[type="range"] {
        flex: 1;
        margin: 0 10px;
    }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="logo">
        <img src="Images/logo.png" alt="HomeFit">
        <div class="logo_name">HomeFit</div>
    </div>
    <i class='bx bx-menu' id='btn'></i>

    <ul class="nav_list">
        <li><a href="Home.html"><i class='bx bx-home-alt-2'></i><span class="links_name">Home</span></a>
            <span class="tooltip">Home</span>
        </li>
        <li><a href="MyExercise.html"><i class='bx bx-run'></i><span class="links_name">My Exercise</span></a>
            <span class="tooltip">My Exercise</span>
        </li>
        <li><a href="Notes.html"><i class='bx bx-note'></i><span class="links_name">Notes</span></a>
            <span class="tooltip">Notes</span></a>
        </li>
        <li><a href="Profile.html"><i class='bx bx-user'></i><span class="links_name">Profile</span></a>
            <span class="tooltip">Profile</span></a>
        </li>
        <li><a href="History.html"><i class='bx bx-history'></i><span class="links_name">History</span></a>
            <span class="tooltip">History</span></a>
        </li>
        <li><a href="index.html"><i class='bx bx-log-out'></i><span class="links_name">Log Out</span></a>
            <span class="tooltip">Log Out</span></a>
        </li>
    </ul>
</div>
<div class="home_content">
    <div class="back-button-container">
        <a href="Profile.html">Back</a>
    </div>
    <div class="text" style="margin-top:-40px;">Updating Profile</div>
    <form action="" method="post" enctype="multipart/form-data">
        <div class="container">
            <div class="profile-container">
                <div class="profile-image-container">
                    <img id="profileImagePreview" src="<?php echo isset($user['profile_image']) ? 'profile_images/' . $user['profile_image'] : 'path/to/default/image.jpg'; ?>" alt="Profile Image">
                    <input type="file" id="profileImageInput" name="update_profile_image" style="display: none;">
                    <label for="profileImageInput" class="add-icon">
                        <i class="fas fa-camera"></i>
                    </label>
                </div>
                <label for="update_username">Username:</label>
                <input type="text" id="update_username" name="update_username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                <label for="update_gender">Gender:</label>
                <select id="update_gender" name="update_gender" required>
                    <option value="Male" <?php if($user['gender'] == 'Male') echo 'selected'; ?>>Male</option>
                    <option value="Female" <?php if($user['gender'] == 'Female') echo 'selected'; ?>>Female</option>
                </select>
                <label for="update_email">Email:</label>
                <input type="email" id="update_email" name="update_email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                <label for="update_year_of_birth">Year of Birth:</label>
                <input type="date" id="update_year_of_birth" name="update_year_of_birth" value="<?php echo htmlspecialchars($user['year_of_birth']); ?>" required>
                <label for="new_pass">New Password:</label>
                <input type="password" id="new_pass" name="new_pass">
                <label for="confirm_pass">Confirm New Password:</label>
                <input type="password" id="confirm_pass" name="confirm_pass">
            </div>
            <div class="survey-container">
                <label for="update_age">Age:</label>
                <input type="number" id="update_age" name="update_age" value="<?php echo htmlspecialchars($survey['age']); ?>">
                <label for="weight">Weight:</label>
                <input type="number" id="weightInput" name="update_weight" min="0" max="200" step="1" value="<?php echo htmlspecialchars($survey['weight']); ?>">
                <label for="height">Height:</label>
                <input type="number" id="heightInput" name="update_height" min="0" max="300" step="1" value="<?php echo htmlspecialchars($survey['height']); ?>">
                <label for="update_fitness_goal">Fitness Goal:</label>
                <input type="text" id="update_fitness_goal" name="update_fitness_goal" value="<?php echo htmlspecialchars($survey['fitness_goal']); ?>">
                <label for="update_health">Health:</label>
                <div id="update_health">
                    <div>
                        <input type="radio" id="health_above_average" name="update_health" value="Above Average" <?php if($survey['health'] == 'Above Average') echo 'checked'; ?>>
                        <label for="health_above_average">Above Average</label>
                    </div>
                    <div>
                        <input type="radio" id="health_average" name="update_health" value="Average" <?php if($survey['health'] == 'Average') echo 'checked'; ?>>
                        <label for="health_average">Average</label>
                    </div>
                    <div>
                        <input type="radio" id="health_below_average" name="update_health" value="Below Average" <?php if($survey['health'] == 'Below Average') echo 'checked'; ?>>
                        <label for="health_below_average">Below Average</label>
                    </div>
                </div>
                <label for="update_gym">Preferred Gym:</label>
                <div id="update_gym">
                    <div>
                        <input type="radio" id="gym_yes" name="update_gym" value="Yes" <?php if($survey['gym'] == 'Yes') echo 'checked'; ?>>
                        <label for="gym_yes">Yes</label>
                    </div>
                    <div>
                        <input type="radio" id="gym_no" name="update_gym" value="No" <?php if($survey['gym'] == 'No') echo 'checked'; ?>>
                        <label for="gym_no">No</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-submit">
            <input type="submit" value="Update">
        </div>
    </form>
</div>

<script>
    let btn = document.querySelector("#btn");
    let sidebar = document.querySelector(".sidebar");

    btn.onclick = function() {
        sidebar.classList.toggle("active");
    }

    document.getElementById('profileImageInput').addEventListener('change', function() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profileImagePreview').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
</script>
</body>
</html>
