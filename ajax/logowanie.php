<?php
header("X-Robots-Tag: noindex, nofollow");

session_start();

include '../connect.php';


$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $has = $_POST['has'];
    $id = $_POST['id'];
    $has = mysqli_real_escape_string($conn, $has);
    $id= mysqli_real_escape_string($conn, $id);
    $allowed_characters = '/^[\p{L}\p{N}\p{P}\p{S}\p{Zs}]+$/u';
    if (preg_match($allowed_characters, $has) && preg_match($allowed_characters, $id)){

        $gg=0;
        $query = "SELECT `id`, `nickname`, `password` FROM users WHERE email = ?";
        $stmt01 = $conn->prepare($query);
        $stmt01->bind_param('s', $id);
        $stmt01->execute();
        $result = $stmt01->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($has, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nickname'] = $user['nickname'];

                $queryRole = "SELECT `user_id` FROM admin_list WHERE user_id = ?";
                $stmtRole = $conn->prepare($queryRole);
                $stmtRole->bind_param('i', $user['id']);
                $stmtRole->execute();
                $resultRole = $stmtRole->get_result();

                if ($resultRole->num_rows > 0) {
                    $_SESSION['user_role'] = 'admin';
                } else {
                    $_SESSION['user_role'] = 'user';
                }

                $gg = 1;
                $notify = "Pomyślnie zalogowano.";
            } else {
                session_regenerate_id(true);
                $notify = "Hasło jest niepoprawne.";
                $gg = 0;
            }
        } else {
            $notify = "Nie znaleziono użytkownika.";
            $gg = 0;
        }

    }


    $conn->close(); 


    echo json_encode($response[] = array(
        'gg' => $gg,
        'notify' => $notify
    ));


}


