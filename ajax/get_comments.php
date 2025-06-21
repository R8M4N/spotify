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

include '../connect.php';
$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Błąd połączenia z bazą danych']);
    exit;
}

$song_id = intval($_POST['song_id']);

try {
    $stmt = $conn->prepare("
        SELECT c.id, c.content, c.created_at, u.nickname, u.id as user_id
        FROM comments c 
        LEFT JOIN users u ON c.user_id = u.id 
        WHERE c.song_id = ? AND c.parent_id IS NULL 
        ORDER BY c.created_at DESC
    ");
    
    if (!$stmt) {
        throw new Exception('Błąd przygotowania zapytania: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $song_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $comments = [];
    while ($row = $result->fetch_assoc()) {
        $comment = [
            'id' => $row['id'],
            'content' => $row['content'],
            'created_at' => $row['created_at'],
            'username' => $row['nickname'] ?? 'Użytkownik usunięty',
            'user_id' => $row['user_id'],
            'replies' => []
        ];
        
        $reply_stmt = $conn->prepare("
            SELECT c.id, c.content, c.created_at, u.nickname, u.id as user_id
            FROM comments c 
            LEFT JOIN users u ON c.user_id = u.id 
            WHERE c.song_id = ? AND c.parent_id = ? 
            ORDER BY c.created_at ASC
        ");
        
        if (!$reply_stmt) {
            throw new Exception('Błąd przygotowania zapytania odpowiedzi: ' . $conn->error);
        }
        
        $reply_stmt->bind_param("ii", $song_id, $comment['id']);
        $reply_stmt->execute();
        $reply_result = $reply_stmt->get_result();
        
        while ($reply_row = $reply_result->fetch_assoc()) {
            $comment['replies'][] = [
                'id' => $reply_row['id'],
                'content' => $reply_row['content'],
                'created_at' => $reply_row['created_at'],
                'username' => $reply_row['nickname'] ?? 'Użytkownik usunięty',
                'user_id' => $reply_row['user_id']
            ];
        }
        
        $reply_stmt->close();
        $comments[] = $comment;
    }
    
    $stmt->close();
    
    echo json_encode([
        'success' => true, 
        'comments' => $comments,
        'total' => count($comments)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Błąd podczas pobierania komentarzy: ' . $e->getMessage()
    ]);
} finally {
    $conn->close();
}
?>