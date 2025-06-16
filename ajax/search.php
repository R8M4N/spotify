<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

include '../connect.php';

function sendResponse($success, $data = null, $message = '') {
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, null, 'Nieprawidłowa metoda żądania');
}

$input = json_decode(file_get_contents('php://input'), true);
if ($input === null) {
    $input = $_POST;
}

if (!isset($input['query']) || empty(trim($input['query']))) {
    sendResponse(false, null, 'Brak zapytania wyszukiwania');
}

$searchQuery = trim($input['query']);

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

try {
    $sql = "SELECT s.id, s.title, s.cover, s.path, u.nickname as artist 
            FROM songs s 
            LEFT JOIN users u ON s.user_id = u.id 
            WHERE s.title LIKE ? OR u.nickname LIKE ? 
            ORDER BY s.title ASC 
            LIMIT 50";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        sendResponse(false, null, 'Błąd przygotowania zapytania');
    }
    
    $searchParam = '%' . $searchQuery . '%';
    
    $stmt->bind_param("ss", $searchParam, $searchParam);
    
    if (!$stmt->execute()) {
        sendResponse(false, null, 'Błąd wykonania zapytania');
    }

    $result = $stmt->get_result();
    $songs = [];
    
    while ($row = $result->fetch_assoc()) {
        $songs[] = [
            'id' => (int)$row['id'],
            'title' => htmlspecialchars($row['title'] ?? '', ENT_QUOTES, 'UTF-8'),
            'artist' => htmlspecialchars($row['artist'] ?? 'Nieznany artysta', ENT_QUOTES, 'UTF-8'),
            'cover' => htmlspecialchars($row['cover'] ?? 'default.jpg', ENT_QUOTES, 'UTF-8'),
            'path' => htmlspecialchars($row['path'] ?? '', ENT_QUOTES, 'UTF-8')
        ];
    }
    
    $stmt->close();
    $conn->close();

    sendResponse(true, $songs, count($songs) . ' wyników znalezionych');
    
} catch (Exception $e) {
    $conn->close();
    sendResponse(false, null, 'Wystąpił błąd podczas wyszukiwania');
}
?>