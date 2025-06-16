<?php
header('Content-Type: application/json');

include '../connect.php';
$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}

$conn->set_charset("utf8");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['playlist_id'])) {
    $playlist_id = intval($_POST['playlist_id']);
    
    try {
        $playlist_query = "SELECT name, cover FROM playlist_main WHERE id = ?";
        $stmt = $conn->prepare($playlist_query);
        $stmt->bind_param("i", $playlist_id);
        $stmt->execute();
        $playlist_result = $stmt->get_result();
        
        if ($playlist_result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Playlista nie została znaleziona']);
            exit;
        }
        
        $playlist_data = $playlist_result->fetch_assoc();
        $stmt->close();
        
        $songs_query = "SELECT song_id FROM playlist_main_songs WHERE playlist_id = ? ORDER BY id";
        $stmt = $conn->prepare($songs_query);
        $stmt->bind_param("i", $playlist_id);
        $stmt->execute();
        $songs_result = $stmt->get_result();
        
        $songs_data = [];
        
        while ($song_row = $songs_result->fetch_assoc()) {
            $song_id = $song_row['song_id'];
            
            $song_query = "SELECT title,`path`, cover, user_id FROM songs WHERE id = ?";
            $song_stmt = $conn->prepare($song_query);
            $song_stmt->bind_param("i", $song_id);
            $song_stmt->execute();
            $song_result = $song_stmt->get_result();
            
            if ($song_result->num_rows > 0) {
                $song_info = $song_result->fetch_assoc();
                
                $user_query = "SELECT nickname FROM users WHERE id = ?";
                $user_stmt = $conn->prepare($user_query);
                $user_stmt->bind_param("i", $song_info['user_id']);
                $user_stmt->execute();
                $user_result = $user_stmt->get_result();
                
                $artist_name = 'Nieznany artysta';
                if ($user_result->num_rows > 0) {
                    $user_info = $user_result->fetch_assoc();
                    $artist_name = $user_info['nickname'];
                }
                
                $songs_data[] = [
                    'title' => $song_info['title'],
                    'path' => $song_info['path'],
                    'cover' => $song_info['cover'],
                    'artist' => $artist_name
                ];
                
                $user_stmt->close();
            }
            
            $song_stmt->close();
        }
        
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'playlist' => [
                'name' => $playlist_data['name'],
                'cover' => $playlist_data['cover']
            ],
            'songs' => $songs_data
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Błąd serwera: ' . $e->getMessage()]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowe żądanie']);
}

$conn->close();
?>