<?php
// Database connection using PDO
try {
    $pdo = new PDO("mysql:host=localhost;dbname=chavings_tasker", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>