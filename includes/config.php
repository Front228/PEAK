<?php
$host = 'localhost';
// $host = '127.0.1.29';
$dbname = 'peak_performance';
$username = 'root';
$password = '';    
// $port = '3006';

$mysqli = new mysqli($host, $username, $password, $dbname);

if ($mysqli->connect_error) {
    die("Ошибка подключения: " . $mysqli->connect_error);
}

$mysqli->set_charset("utf8mb4");
?>