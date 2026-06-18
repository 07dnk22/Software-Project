<?php
include 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    
    if (!empty($message)) {
        // Sanitize the message to prevent SQL injection
        $message = $conn->real_escape_string($message);
        
        $sql = "INSERT INTO messages (message) VALUES ('$message')";
        
        if ($conn->query($sql) === TRUE) {
            header("Location: message.html?status=success");
            exit();
        } else {
            header("Location: message.html?status=error");
            exit();
        }
    } else {
        header("Location: message.html?status=empty");
        exit();
    }
} else {
    header("Location: message.html");
    exit();
}

$conn->close();
?>