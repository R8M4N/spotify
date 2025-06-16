<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Użytkownik nie jest zalogowany'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

include '../connect.php';
$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Błąd połączenia z bazą danych'
    ]);
    exit;
}

try {
    $sql = "SELECT 
                s.id,
                s.title,
                s.cover,
                s.path,
                u.nickname as artist
            FROM likes l
            INNER JOIN songs s ON l.song_id = s.id
            INNER JOIN users u ON s.user_id = u.id
            WHERE l.user_id = ?
            ORDER BY s.title ASC";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Błąd przygotowania zapytania: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $liked_songs = [];
    while ($row = $result->fetch_assoc()) {
        $liked_songs[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'artist' => $row['artist'],
            'cover' => $row['cover'],
            'path' => $row['path']
        ];
    }
    
    $stmt->close();
    echo json_encode([
        'success' => true,
        'songs' => $liked_songs,
        'count' => count($liked_songs)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Błąd podczas pobierania danych.'
    ]);
} finally {
    $conn->close();
}
?>