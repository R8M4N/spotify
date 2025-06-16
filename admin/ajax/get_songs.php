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

$search = isset($_GET['search']) ? $_GET['search'] : '';

try {
    $sql = "SELECT s.id, s.title, s.cover, s.path, u.nickname 
            FROM songs s 
            LEFT JOIN users u ON s.user_id = u.id";
    
    if (!empty($search)) {
        $sql .= " WHERE s.title LIKE ? OR u.nickname LIKE ?";
        $stmt = $conn->prepare($sql);
        $searchParam = "%$search%";
        $stmt->bind_param("ss", $searchParam, $searchParam);
    } else {
        $stmt = $conn->prepare($sql);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $songs = [];
    while ($row = $result->fetch_assoc()) {
        $songs[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $songs]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Błąd podczas pobierania piosenek']);
}

$conn->close();
?>