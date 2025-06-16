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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowa metoda żądania']);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowe ID piosenki']);
    exit;
}

try {
    $conn->autocommit(false);
    
    $stmt = $conn->prepare("DELETE FROM playlist_main_songs WHERE song_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $stmt = $conn->prepare("DELETE FROM songs WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $logContent = "Usunięto piosenkę ID: $id";
        $logStmt = $conn->prepare("INSERT INTO admin_logs (user_id, content) VALUES (?, ?)");
        $logStmt->bind_param("is", $_SESSION['user_id'], $logContent);
        $logStmt->execute();
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Piosenka została usunięta']);
    } else {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Błąd podczas usuwania']);
    }
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Błąd podczas usuwania piosenki']);
}

$conn->close();
?>