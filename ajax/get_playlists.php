<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

include '../connect.php';
$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowa metoda żądania']);
    exit;
}

try {
    $query = "SELECT id, name FROM playlist_main ORDER BY name ASC";
    $stmt01 = $conn->prepare($query);
    $stmt01->execute();
    $result = $stmt01->get_result();
    $playlists = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode([
        'success' => true,
        'playlists' => $playlists
    ]);
    
} catch (Exception $e) {
    error_log("Błąd podczas pobierania playlist: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Wystąpił błąd podczas pobierania playlist']);
}
?>