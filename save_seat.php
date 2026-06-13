<?php
session_start();
include 'connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$email = $_SESSION['email'];
$office = $_POST['office'] ?? '';
$seat_number = $_POST['seat_number'] ?? '';

if (!in_array($office, ['A', 'B'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid office']);
    exit();
}

$seat_number = intval($seat_number);

if ($office === 'A' && ($seat_number < 1 || $seat_number > 12)) {
    echo json_encode(['success' => false, 'message' => 'Invalid seat number for Office A']);
    exit();
}

if ($office === 'B' && ($seat_number < 1 || $seat_number > 24)) {
    echo json_encode(['success' => false, 'message' => 'Invalid seat number for Office B']);
    exit();
}

// Check if this seat is already taken by another user
$checkStmt = $conn->prepare("SELECT email FROM seats WHERE office = ? AND seat_number = ? AND email != ?");
$checkStmt->bind_param("sis", $office, $seat_number, $email);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'This seat is already taken by another user']);
    $checkStmt->close();
    $conn->close();
    exit();
}
$checkStmt->close();

// Check if user already has a seat
$existingStmt = $conn->prepare("SELECT office, seat_number FROM seats WHERE email = ?");
$existingStmt->bind_param("s", $email);
$existingStmt->execute();
$existingResult = $existingStmt->get_result();
$existingRow = $existingResult->fetch_assoc();
$existingStmt->close();

if ($existingRow) {
    $oldOffice = $existingRow['office'];
    $oldSeat = $existingRow['seat_number'];
    
    $updateStmt = $conn->prepare("UPDATE seats SET office = ?, seat_number = ? WHERE email = ?");
    $updateStmt->bind_param("sis", $office, $seat_number, $email);
    
    if ($updateStmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => "Moved from Office $oldOffice - Seat $oldSeat to Office $office - Seat $seat_number",
            'previous' => ['office' => $oldOffice, 'seat' => $oldSeat]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
    $updateStmt->close();
} else {
    $insertStmt = $conn->prepare("INSERT INTO seats (email, office, seat_number) VALUES (?, ?, ?)");
    $insertStmt->bind_param("ssi", $email, $office, $seat_number);
    
    if ($insertStmt->execute()) {
        echo json_encode(['success' => true, 'message' => "Seat $seat_number in Office $office saved!"]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
    $insertStmt->close();
}

$conn->close();
?>