<?php
// параметры подключения к БД
$host = "127.0.1.17";
$db = "pharmacy";
$user = "root";
$password = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;port=3306;dbname=$db;charset=utf8",
        $user,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("DB error: " . $e->getMessage());
}