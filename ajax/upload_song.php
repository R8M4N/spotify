<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

include '../connect.php';
$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowa metoda żądania']);
    exit;
}

if (empty($_POST['title'])) {
    echo json_encode(['success' => false, 'message' => 'Tytuł jest wymagany']);
    exit;
}

if (!isset($_FILES['cover']) || $_FILES['cover']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Okładka jest wymagana']);
    exit;
}

if (!isset($_FILES['audio']) || $_FILES['audio']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Plik audio jest wymagany']);
    exit;
}

$title = trim($_POST['title']);
$playlist_id = !empty($_POST['playlist_id']) ? intval($_POST['playlist_id']) : null;
$user_id = $_SESSION['user_id']; 

$covers_dir = '../covers/';
$audio_dir = '../songs/';

if (!file_exists($covers_dir)) {
    mkdir($covers_dir, 0755, true);
}
if (!file_exists($audio_dir)) {
    mkdir($audio_dir, 0755, true);
}

function generateUniqueFileName($extension) {
    return uniqid(mt_rand(), true) . '.' . $extension;
}

function validateImage($file) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 10 * 1024 * 1024; 
    
    if (!in_array($file['type'], $allowed_types)) {
        return 'Niedozwolony typ pliku okładki. Dozwolone: JPG, PNG, GIF, WebP';
    }
    
    if ($file['size'] > $max_size) {
        return 'Plik okładki jest za duży (maksymalnie 10MB)';
    }
    
    $image_info = getimagesize($file['tmp_name']);
    if ($image_info === false) {
        return 'Wybrany plik nie jest prawidłowym obrazem';
    }
    
    return null;
}

function validateAudio($file) {
    $allowed_types = ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/flac', 'audio/ogg'];
    $max_size = 50 * 1024 * 1024; 
    
    if (!in_array($file['type'], $allowed_types)) {
        return 'Niedozwolony typ pliku audio. Dozwolone: MP3, WAV, FLAC, OGG';
    }
    
    if ($file['size'] > $max_size) {
        return 'Plik audio jest za duży (maksymalnie 50MB)';
    }
    
    return null;
}

function makeSquareImage($source_path, $destination_path, $size = 500) {
    $image_info = getimagesize($source_path);
    $mime_type = $image_info['mime'];
    
    switch ($mime_type) {
        case 'image/jpeg':
            $source = imagecreatefromjpeg($source_path);
            break;
        case 'image/png':
            $source = imagecreatefrompng($source_path);
            break;
        case 'image/gif':
            $source = imagecreatefromgif($source_path);
            break;
        case 'image/webp':
            $source = imagecreatefromwebp($source_path);
            break;
        default:
            return false;
    }
    
    if (!$source) {
        return false;
    }
    
    $width = imagesx($source);
    $height = imagesy($source);
    
    $crop_size = min($width, $height);
    $crop_x = ($width - $crop_size) / 2;
    $crop_y = ($height - $crop_size) / 2;
    
    $destination = imagecreatetruecolor($size, $size);
    
    if ($mime_type === 'image/png' || $mime_type === 'image/gif') {
        imagealphablending($destination, false);
        imagesavealpha($destination, true);
        $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
        imagefill($destination, 0, 0, $transparent);
    }
    
    imagecopyresampled(
        $destination, $source,
        0, 0, $crop_x, $crop_y,
        $size, $size, $crop_size, $crop_size
    );
    
    $result = imagejpeg($destination, $destination_path, 90);
    
    imagedestroy($source);
    imagedestroy($destination);
    
    return $result;
}

try {
    $cover_error = validateImage($_FILES['cover']);
    if ($cover_error) {
        echo json_encode(['success' => false, 'message' => $cover_error]);
        exit;
    }
    
    $audio_error = validateAudio($_FILES['audio']);
    if ($audio_error) {
        echo json_encode(['success' => false, 'message' => $audio_error]);
        exit;
    }
    
    $cover_extension = 'jpg'; 
    $audio_extension = pathinfo($_FILES['audio']['name'], PATHINFO_EXTENSION);
    
    $cover_filename = generateUniqueFileName($cover_extension);
    $audio_filename = generateUniqueFileName($audio_extension);
    
    $cover_path = $covers_dir . $cover_filename;
    $audio_path = $audio_dir . $audio_filename;
    
    if (!makeSquareImage($_FILES['cover']['tmp_name'], $cover_path)) {
        echo json_encode(['success' => false, 'message' => 'Błąd podczas przetwarzania okładki']);
        exit;
    }
    
    if (!move_uploaded_file($_FILES['audio']['tmp_name'], $audio_path)) {
        if (file_exists($cover_path)) {
            unlink($cover_path);
        }
        echo json_encode(['success' => false, 'message' => 'Błąd podczas przesyłania pliku audio']);
        exit;
    }
    
    $conn->begin_transaction();

    $stmt = $conn->prepare("INSERT INTO songs (title, user_id, cover, path) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siss", $title, $user_id, $cover_filename, $audio_filename);
    $stmt->execute();

    $song_id = $conn->insert_id;

    if ($playlist_id) {
        $stmt = $conn->prepare("SELECT id FROM playlist_main WHERE id = ?");
        $stmt->bind_param("i", $playlist_id);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt = $conn->prepare("INSERT INTO playlist_main_songs (playlist_id, song_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $playlist_id, $song_id);
            $stmt->execute();
        }
    }

    $conn->commit();

    
    echo json_encode([
        'success' => true,
        'message' => 'Piosenka została dodana pomyślnie',
        'song_id' => $song_id,
        'cover' => $cover_filename,
        'audio' => $audio_filename
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    
    if (isset($cover_path) && file_exists($cover_path)) {
        unlink($cover_path);
    }
    if (isset($audio_path) && file_exists($audio_path)) {
        unlink($audio_path);
    }
    
    error_log("Błąd podczas dodawania piosenki: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Wystąpił błąd podczas dodawania piosenki']);
}
?>