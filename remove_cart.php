<?php
session_start();

$host = 'localhost';
$dbname = 'UTDMart';
$username = 'admin';
$password = 'password';

// Establishing Connection with Database
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetching data from the form
$itemName = $_POST['itemName'];
echo $itemName;

$itemDetailQuery = "SELECT * FROM Inventory WHERE Name = ?";
$stmt = $conn->prepare($itemDetailQuery);

// Bind parameters and execute
$stmt->bind_param("s", $itemName);
$stmt->execute();

// Fetch the results
$itemDetails = $stmt->get_result();
$item = $itemDetails->fetch_assoc();
// Get item details from inventory table
$itemPrice = $item['UnitPrice'];
$itemNumber = $item['ItemNumber'];
$itemCategory = $item['Category'];
$customerID = isset($_SESSION['customerID']) ? $_SESSION['customerID'] : '';
$transactionID = isset($_SESSION['transactionID']) ? $_SESSION['transactionID'] : '';

// Sanitizing input to prevent SQL Injection
$itemName = $conn->real_escape_string($itemName);
$itemPrice = $conn->real_escape_string($itemPrice);
$itemCategory = $conn->real_escape_string($itemCategory);
$customerID = $conn->real_escape_string($customerID);
$transactionID = $conn->real_escape_string($transactionID);
$itemNumber = $conn->real_escape_string($itemNumber);

// Check if the item is already in the cart
$cartCheckQuery = "SELECT * FROM Carts WHERE CustomerID = '$customerID' AND ItemNumber = '$itemNumber'";
$cartResult = $conn->query($cartCheckQuery);

$row = $cartResult->fetch_assoc();
$inventoryUpdated = false;  // flag to check if inventory needs to be updated

if ($row['Quantity'] == 1) {
    // Prepare a statement for deleting the item
    $updateCartQuery = "DELETE FROM Carts WHERE CustomerID = ? AND ItemNumber = ?";
    $stmt = $conn->prepare($updateCartQuery);
    $stmt->bind_param("ss", $customerID, $itemNumber);
    $inventoryUpdated = true;
} else {
    // Reduce the quantity by 1
    $newQuantity = $row['Quantity'] - 1;
    // Prepare a statement for updating the quantity
    $updateCartQuery = "UPDATE Carts SET Quantity = ? WHERE CustomerID = ? AND ItemNumber = ?";
    $stmt = $conn->prepare($updateCartQuery);
    $stmt->bind_param("iss", $newQuantity, $customerID, $itemNumber);
    if ($newQuantity < $row['Quantity']) {
        $inventoryUpdated = true;
    }
}

// Execute the prepared statement
$stmt->execute();

// Check for errors (optional, but recommended)
if ($stmt->error) {
    // Handle error
    echo "Error: " . $stmt->error;
} else {
    echo "Operation successful.";
}

// Close the statement
$stmt->close();

// Increase inventory if item was deleted or quantity reduced
if ($inventoryUpdated) {
    $updateInventoryQuery = "UPDATE Inventory SET Quantity = Quantity + 1 WHERE ItemNumber = '$itemNumber'";
    $conn->query($updateInventoryQuery);
}

$conn->close();

// Redirect back to the product page or you can show a success message
//header('Location: fresh_products.html');
// echo "Item added to cart successfully.";
?>
