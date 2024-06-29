<?php
session_start();

$host = 'localhost';
$dbname = 'UTDMart';
$username = 'admin';
$password = 'password';

if (!isset($_SESSION['username']) || !isset($_SESSION['cart'])) {
    header("Location: login.php");
    exit;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    foreach ($_SESSION['cart'] as $itemNumber => $quantity) {
        $stmt = $pdo->prepare("INSERT INTO Transactions (CustomerID, ItemNumber, Quantity, Status) VALUES (?, ?, ?, 'Purchased')");
        $stmt->execute([$_SESSION['username'], $itemNumber, $quantity]); // Assuming CustomerID is same as Username
    }

    // Clear the cart after checkout
    $_SESSION['cart'] = array();

    echo "<script>alert('Purchase successful!');</script>";

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
