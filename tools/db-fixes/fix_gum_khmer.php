<?php
$host = '127.0.0.1';
$db   = 'printing_system';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    $stmt = $pdo->prepare("UPDATE materials SET name_km = :name_km WHERE name = :name");
    $stmt->execute([
        'name_km' => 'ទឹកថ្នាំ ហ្គូម',
        'name' => 'Gum Solution'
    ]);

    echo "Successfully updated Gum Solution Khmer translation.\n";
} catch (\PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
