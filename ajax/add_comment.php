<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Musisz być zalogowany']);
    exit;
}

if(!isset($_POST['song_id']) || empty($_POST['song_id'])) {
    echo json_encode(['success' => false, 'message' => 'Brak ID piosenki']);
    exit;
}

if(!isset($_POST['content']) || empty(trim($_POST['content']))) {
    echo json_encode(['success' => false, 'message' => 'Komentarz nie może być pusty']);
    exit;
}

include '../connect.php';
$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Błąd połączenia z bazą danych']);
    exit;
}

$userId = $_SESSION['user_id'];
$song_id = intval($_POST['song_id']);
$content = trim($_POST['content']);
$parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : null;

if (strlen($content) > 1000) {
    echo json_encode(['success' => false, 'message' => 'Komentarz jest za długi (maksymalnie 1000 znaków)']);
    $conn->close();
    exit;
}

if ($parent_id !== null) {
    $check_stmt = $conn->prepare("SELECT id FROM comments WHERE id = ? AND song_id = ? AND parent_id IS NULL");
    if (!$check_stmt) {
        echo json_encode(['success' => false, 'message' => 'Błąd sprawdzania komentarza nadrzędnego']);
        $conn->close();
        exit;
    }
    
    $check_stmt->bind_param("ii", $parent_id, $song_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Nie można odpowiedzieć na nieistniejący komentarz']);
        $check_stmt->close();
        $conn->close();
        exit;
    }
    $check_stmt->close();
}

try {
    $stmt = $conn->prepare("INSERT INTO comments (song_id, user_id, parent_id, content) VALUES (?, ?, ?, ?)");
    
    if (!$stmt) {
        throw new Exception('Błąd przygotowania zapytania: ' . $conn->error);
    }
    
    $stmt->bind_param("iiis", $song_id, $userId, $parent_id, $content);
    
    if (!$stmt->execute()) {
        throw new Exception('Błąd podczas zapisywania komentarza: ' . $stmt->error);
    }
    
    $comment_id = $conn->insert_id;
    $stmt->close();
    
    $get_stmt = $conn->prepare("
        SELECT c.id, c.content, c.created_at, u.nickname, u.id as user_id
        FROM comments c 
        LEFT JOIN users u ON c.user_id = u.id 
        WHERE c.id = ?
    ");
    
    if (!$get_stmt) {
        throw new Exception('Błąd pobierania dodanego komentarza');
    }
    
    $get_stmt->bind_param("i", $comment_id);
    $get_stmt->execute();
    $result = $get_stmt->get_result();
    $comment_data = $result->fetch_assoc();
    $get_stmt->close();
    
    if (!$comment_data) {
        throw new Exception('Nie udało się pobrać dodanego komentarza');
    }
    
    echo json_encode([
        'success' => true, 
        'message' => $parent_id ? 'Odpowiedź została dodana' : 'Komentarz został dodany',
        'comment' => [
            'id' => $comment_data['id'],
            'content' => $comment_data['content'],
            'created_at' => $comment_data['created_at'],
            'username' => $comment_data['nickname'],
            'user_id' => $comment_data['user_id'],
            'parent_id' => $parent_id
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Błąd podczas dodawania komentarza: ' . $e->getMessage()
    ]);
} finally {
    $conn->close();
}
?>