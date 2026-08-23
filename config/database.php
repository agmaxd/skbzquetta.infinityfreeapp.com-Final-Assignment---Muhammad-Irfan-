<?php

$host = "sql202.infinityfree.com";
$dbname = "if0_42728676_hospital_db";
$username = "if0_42728676";
$password = "GloriouS123";

try {

    $pdo = new PDO(
        "mysql:host=$host;port=3306;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $pdo->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

} catch (PDOException $e) {

    die("Database connection failed: " . $e->getMessage());

}