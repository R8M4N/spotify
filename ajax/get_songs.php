<?php
include '../connect.php';
$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}

$query = "
    SELECT songs.path, songs.cover, songs.title, songs.id, users.nickname 
    FROM songs 
    INNER JOIN users ON songs.user_id = users.id 
    ORDER BY RAND() 
    LIMIT 7
";
$stmt01 = $conn->prepare($query);
$stmt01->execute();
$result = $stmt01->get_result();
$randomSongs = $result->fetch_all(MYSQLI_ASSOC);
$stmt01->close();

$playlists = [];
$queryPlaylists = "SELECT id, name FROM playlist_main ORDER BY RAND()";
$resultPlaylists = $conn->query($queryPlaylists);

if ($resultPlaylists && $resultPlaylists->num_rows > 0) {
    while ($playlist = $resultPlaylists->fetch_assoc()) {
        $playlistId = $playlist['id'];
        $playlistName = $playlist['name'];

        $querySongs = "
            SELECT pms.song_id, s.path, s.cover, s.id, s.title, u.nickname 
            FROM playlist_main_songs pms
            INNER JOIN songs s ON pms.song_id = s.id
            INNER JOIN users u ON s.user_id = u.id
            WHERE pms.playlist_id = ?
        ";

        $stmtSongs = $conn->prepare($querySongs);
        $stmtSongs->bind_param("i", $playlistId);
        $stmtSongs->execute();
        $resultSongs = $stmtSongs->get_result();
        $songsInPlaylist = $resultSongs->fetch_all(MYSQLI_ASSOC);
        $stmtSongs->close();

        $playlists[] = [
            'id' => $playlistId,
            'name' => $playlistName,
            'songs' => $songsInPlaylist
        ];
    }
}

$conn->close();

$response = [
    'randomSongs' => $randomSongs,
    'playlists' => $playlists
];

echo json_encode($response);
?>
