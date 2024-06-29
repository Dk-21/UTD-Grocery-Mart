<?php
session_start();
$host = 'localhost';
$dbname = 'UTDMart';
$username = 'admin';
$password = 'password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if session variables are set
    if (!isset($_SESSION['customerID']) || !isset($_SESSION['transactionID'])) {
        throw new Exception("Session variables for CustomerID or TransactionID are not set.");
    }

    $customerID = $_SESSION['customerID'];
    $transactionID = $_SESSION['transactionID'];

    // Validate POST data
    if (!isset($_POST['itemNumber']) || !isset($_POST['quantity'])) {
        throw new Exception("POST data for itemNumber or quantity is not set.");
    }

    $itemNumber = $_POST['itemNumber'];
    $quantity = $_POST['quantity'];

    // Check if the item already exists in the cart
    $stmtCheck = $pdo->prepare("SELECT * FROM Carts WHERE CustomerID = ? AND TransactionID = ? AND ItemNumber = ?");
    $stmtCheck->execute([$customerID, $transactionID, $itemNumber]);
    $existingItem = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($existingItem) {
        // Item exists, update quantity
        $newQuantity = $existingItem['Quantity'] + $quantity;
        $stmtUpdate = $pdo->prepare("UPDATE Carts SET Quantity = ? WHERE CustomerID = ? AND TransactionID = ? AND ItemNumber = ?");
        $stmtUpdate->execute([$newQuantity, $customerID, $transactionID, $itemNumber]);
    } else {
        // Item does not exist, insert new record
        $stmtInsert = $pdo->prepare("INSERT INTO Carts (CustomerID, TransactionID, ItemNumber, Quantity, CartStatus) VALUES (?, ?, ?, ?, 'Active')");
        $stmtInsert->execute([$customerID, $transactionID, $itemNumber, $quantity]);
    }

    echo "<script>alert('Item added to cart successfully!');</script>";

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die("Database error: " . $e->getMessage());
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    die("Error: " . $e->getMessage());
}
?>
