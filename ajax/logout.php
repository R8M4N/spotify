<?php
header("X-Robots-Tag: noindex, nofollow");

session_start();

if (isset($_SESSION['user_id']) || !empty($_SESSION['user_id'])) {unset($_SESSION['user_id']);}
if (isset($_SESSION['user_nickname']) || !empty($_SESSION['user_nickname'])) {unset($_SESSION['user_nickname']);}

//session_unset();
//session_destroy();

header('Clear-Site-Data: "cache", "storage"');