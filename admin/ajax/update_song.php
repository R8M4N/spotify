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
$title = isset($_POST['title']) ? trim($_POST['title']) : '';

if ($id <= 0 || empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowe dane']);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE songs SET title = ? WHERE id = ?");
    $stmt->bind_param("si", $title, $id);
    
    if ($stmt->execute()) {
        $logContent = "Zaktualizowano tytuł piosenki ID: $id na \"$title\"";
        $logStmt = $conn->prepare("INSERT INTO admin_logs (user_id, content) VALUES (?, ?)");
        $logStmt->bind_param("is", $_SESSION['user_id'], $logContent);
        $logStmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Piosenka została zaktualizowana']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Błąd podczas aktualizacji']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Błąd podczas aktualizacji piosenki']);
}

$conn->close();
?>