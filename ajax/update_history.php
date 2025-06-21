<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

if (!isset($_POST['song_id']) || empty($_POST['song_id'])) {
    echo json_encode(['success' => false, 'message' => 'Song ID is required']);
    exit;
}

include '../connect.php';
$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$userId = $_SESSION['user_id'];
$songId = intval($_POST['song_id']);

$checkSong = $conn->prepare("SELECT id FROM songs WHERE id = ?");
$checkSong->bind_param("i", $songId);
$checkSong->execute();
$result = $checkSong->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Song not found']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO history (user_id, song_id, time) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE time = NOW()");
$stmt->bind_param("ii", $userId, $songId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'History updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update history']);
}

$stmt->close();
$checkSong->close();
$conn->close();
?>