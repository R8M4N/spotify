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

$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;

try {
    $sql = "SELECT al.id, al.content, al.user_id, u.nickname,
            DATE_FORMAT(al.id, '%Y-%m-%d %H:%i:%s') as created_at
            FROM admin_logs al 
            LEFT JOIN users u ON al.user_id = u.id
            ORDER BY al.id DESC 
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $logs]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Błąd podczas pobierania logów']);
}

$conn->close();
?>
