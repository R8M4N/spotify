<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

include '../connect.php';
$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT s.id, s.title, s.cover, s.path, u.nickname as artist, h.time 
    FROM history h 
    JOIN songs s ON h.song_id = s.id 
    JOIN users u ON s.user_id = u.id 
    WHERE h.user_id = ? 
    ORDER BY h.time DESC 
    LIMIT 50
");

$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$history = [];
while ($row = $result->fetch_assoc()) {
    $history[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'artist' => $row['artist'],
        'cover' => $row['cover'],
        'path' => $row['path'],
        'time' => $row['time']
    ];
}

echo json_encode(['success' => true, 'data' => $history]);

$stmt->close();
$conn->close();
?>