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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['song_id']) && isset($_POST['action'])) {
    $userId = $_SESSION['user_id'];
    $songId = intval($_POST['song_id']);
    $action = $_POST['action'];
    
    if (!in_array($action, ['like', 'unlike'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Nieprawidłowa akcja'
        ]);
        $conn->close();
        exit;
    }
    
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
    
    if ($action === 'like') {
        $stmt = $conn->prepare("INSERT IGNORE INTO likes (user_id, song_id) VALUES (?, ?)");
        if (!$stmt) {
            echo json_encode([
                'success' => false,
                'message' => 'Błąd przygotowania zapytania: ' . $conn->error
            ]);
            $conn->close();
            exit;
        }
        
        $stmt->bind_param("ii", $userId, $songId);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Piosenka została polubiona',
                'action' => 'liked'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Błąd podczas dodawania polubienia: ' . $stmt->error
            ]);
        }
        $stmt->close();
        
    } else {
        $stmt = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND song_id = ?");
        if (!$stmt) {
            echo json_encode([
                'success' => false,
                'message' => 'Błąd przygotowania zapytania: ' . $conn->error
            ]);
            $conn->close();
            exit;
        }
        
        $stmt->bind_param("ii", $userId, $songId);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Polubienie zostało usunięte',
                'action' => 'unliked'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Błąd podczas usuwania polubienia: ' . $stmt->error
            ]);
        }
        $stmt->close();
    }
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Brak wymaganych danych'
    ]);
}

$conn->close();
?>