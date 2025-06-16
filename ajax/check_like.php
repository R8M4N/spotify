<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Użytkownik niezalogowany'
    ]);
    exit;
}

include '../connect.php';
$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => "Błąd połączenia: " . $conn->connect_error
    ]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['song_id'])) {
    $userId = $_SESSION['user_id'];
    $songId = intval($_POST['song_id']);
    
    $checkStmt = $conn->prepare("SELECT id FROM songs WHERE id = ?");
    if (!$checkStmt) {
        echo json_encode([
            'success' => false,
            'message' => 'Błąd przygotowania zapytania: ' . $conn->error
        ]);
        $conn->close();
        exit;
    }
    
    $checkStmt->bind_param("i", $songId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Piosenka nie istnieje'
        ]);
        $checkStmt->close();
        $conn->close();
        exit;
    }
    $checkStmt->close();
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM likes WHERE user_id = ? AND song_id = ?");
    if (!$stmt) {
        echo json_encode([
            'success' => false,
            'message' => 'Błąd przygotowania zapytania: ' . $conn->error
        ]);
        $conn->close();
        exit;
    }
    
    $stmt->bind_param("ii", $userId, $songId);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $isLiked = $data['count'] > 0;
    
    echo json_encode([
        'success' => true,
        'is_liked' => $isLiked
    ]);
    
    $stmt->close();
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Brak wymaganych danych'
    ]);
}

$conn->close();
?>