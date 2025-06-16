<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Nie jesteś zalogowany']);
    exit;
}

include '../../connect.php';
$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT user_id FROM admin_list WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Brak uprawnień administratora']);
    exit;
}

try {
    $users_result = $conn->query("SELECT COUNT(*) as count FROM users");
    $users_count = $users_result->fetch_assoc()['count'];

    $songs_result = $conn->query("SELECT COUNT(*) as count FROM songs");
    $songs_count = $songs_result->fetch_assoc()['count'];

    $playlists_result = $conn->query("SELECT COUNT(*) as count FROM playlist_main");
    $playlists_count = $playlists_result->fetch_assoc()['count'];

    $today_result = $conn->query("
        SELECT COUNT(*) as count 
        FROM admin_logs 
        WHERE created >= CURDATE() 
        AND created < CURDATE() + INTERVAL 1 DAY
    ");
    $today_activity = $today_result->fetch_assoc()['count'];

    echo json_encode([
        'success' => true,
        'data' => [
            'users_count' => $users_count,
            'songs_count' => $songs_count,
            'playlists_count' => $playlists_count,
            'today_activity' => $today_activity
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Błąd podczas pobierania statystyk']);
}

$conn->close();
?>