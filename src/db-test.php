<?php

$host = "mysql";
$dbname = getenv("MYSQL_DATABASE");
$username = getenv("MYSQL_USER");
$password = getenv("MYSQL_PASSWORD");

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

    $pdo = new PDO($dsn, $username, $password);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h1>Database connection successful!</h1>";
    echo "<p>PHP successfully connected to MySQL.</p>";

} catch (PDOException $e) {
    echo "<h1>Database connection failed!</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}