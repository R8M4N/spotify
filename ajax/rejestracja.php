<?php

include '../connect.php';


session_start();

header("X-Robots-Tag: noindex, nofollow");     


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $mail = $_POST['mail'];
    $pass1 = $_POST['pass1'];
    $nic = $_POST['nic'];
    $mail= mysqli_real_escape_string($conn, $mail);
    $pass1= mysqli_real_escape_string($conn, $pass1);
    $nic= mysqli_real_escape_string($conn, $nic);
    
    $gg = 0;
                     
    $options = [
        'cost' => 14,
    ];
    $hashedPassword = password_hash($pass1, PASSWORD_BCRYPT, $options);

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $mail);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $noti= "Konto z podanym e-mail już istnieje.";
    } else {

        $query = "INSERT INTO `users` (`email`, `password`, `nickname`) 
        VALUES (?,?,?)";
        $stmt01 = $conn->prepare($query);
        $stmt01->bind_param('sss', $mail, $hashedPassword, $nic);                                                          
        if ($stmt01->execute()  ) {
            $gg = 1;
            $noti= "Poprawnie zarejestrowano konto. Możesz się teraz zalogować.";
        } else {
            $noti= "Błąd podczas zapisywania danych.";
        }
    }
                                                                          
    $conn->close();
    echo json_encode($response[] = array(
        'notify' => $noti,
        'g' => $gg
    ));
    
}

