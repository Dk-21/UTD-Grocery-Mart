<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection details
$host = 'localhost';
$dbname = 'UTDMart';
$username = 'admin';
$password = 'password';
$conn = new mysqli($host, $username, $password, $dbname);

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch TransactionID from session
$transactionID = isset($_SESSION['transactionID']) ? $_SESSION['transactionID'] : '';
if (!$transactionID) {
    echo "Transaction ID is not set in the session.";
    exit;
}

// Calculate the total price from the cart
$cartQuery = $conn->prepare("
    SELECT SUM(Inventory.UnitPrice * Carts.Quantity) AS TotalPrice 
    FROM Carts 
    JOIN Inventory ON Carts.ItemNumber = Inventory.ItemNumber 
    WHERE Carts.TransactionID = ? AND Carts.CartStatus = 'pending'
");
$cartQuery->bind_param("s", $transactionID);
$cartQuery->execute();
$cartResult = $cartQuery->get_result();
$row = $cartResult->fetch_assoc();
$totalPrice = $row['TotalPrice'];

if (!$totalPrice) {
    echo "No items found in the cart for the provided Transaction ID.";
    exit;
}

// Insert into Transactions table
$currentDate = date('Y-m-d H:i:s'); // Current date and time
$insertTransactionQuery = $conn->prepare("INSERT INTO Transactions (TransactionID, TransactionStatus, TransactionDate, TotalPrice) VALUES (?, 'completed', ?, ?)");
$insertTransactionQuery->bind_param("ssd", $transactionID, $currentDate, $totalPrice);
if (!$insertTransactionQuery->execute()) {
    echo "Error inserting into transactions: " . $insertTransactionQuery->error;
}

// Update Carts table
$updateCartQuery = $conn->prepare("UPDATE Carts SET CartStatus = 'done' WHERE TransactionID = ?");
$updateCartQuery->bind_param("s", $transactionID);
if (!$updateCartQuery->execute()) {
    echo "Error updating carts: " . $updateCartQuery->error;
}

// Optional: Clear the cart by removing session variables or redirecting
// unset($_SESSION['transactionID']); // Uncomment to clear the transaction from session

$conn->close();

// Redirect to empty cart page or show a success message
// header('Location: cart.html'); // Uncomment to redirect to the cart page which should now be empty
?>
