<?php
// Database connection using PDO
try {
    $pdo = new PDO("mysql:host=db.pxxl.pro; port=5795;dbname=db_d3eba46b", "root", "be90d1e513b38df4f8a6375365c686de");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

?>

