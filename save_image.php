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

if (isset($_SESSION['username'])) {
    $username = $conn->real_escape_string($_SESSION['username']);
    $targetDir = "profile_images/";

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $filePath = '';
    $imageSource = isset($_POST['image_source']) ? $_POST['image_source'] : '';

    if ($imageSource === 'file') {
        // Handle file uploads
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profile_image'];
            $fileName = uniqid() . '-' . basename($file['name']);
            $filePath = $targetDir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                echo "File uploaded successfully.";
            } else {
                echo "Failed to upload file.";
                exit;
            }
        }
    } elseif ($imageSource === 'webcam') {
        // Handle base64-encoded images
        if (isset($_POST['profile_image_base64'])) {
            $imageData = $_POST['profile_image_base64'];

            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $type = strtolower($type[1]);

                if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif'])) {
                    die('Invalid image type.');
                }

                $imageData = base64_decode($imageData);
                if ($imageData === false) {
                    die('Base64 decode failed.');
                }

                $fileName = 'profile-' . uniqid() . '.' . $type;
                $filePath = $targetDir . $fileName;

                if (file_put_contents($filePath, $imageData) === false) {
                    die('Failed to save image.');
                }
            } else {
                die('Invalid image data.');
            }
        }
    }

    if ($filePath !== '') {
        $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE username = ?");
        $stmt->bind_param("ss", $filePath, $username);

        if ($stmt->execute()) {
            echo "Profile image updated successfully.";
        } else {
            echo "Error updating record: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "No image data found.";
    }
} 

$conn->close();
?>
