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
$itemQuantity = $_POST['itemQuantity']; // Assuming this is the quantity being added to the cart
echo $itemName;

$itemDetailQuery = "SELECT * FROM Inventory WHERE Name = ?";
$stmt = $conn->prepare($itemDetailQuery);

// Bind parameters and execute
$stmt->bind_param("s", $itemName);
$stmt->execute();

// Fetch the results
$itemDetails = $stmt->get_result();
$item = $itemDetails->fetch_assoc();

// Check if item exists in the inventory
if ($item) {
    // Get item details from inventory table
    $itemPrice = $item['UnitPrice'];
    $itemNumber = $item['ItemNumber'];
    $itemCategory = $item['Category'];
    $currentInventoryQuantity = $item['Quantity'];

    // Sanitizing input to prevent SQL Injection
    $itemName = $conn->real_escape_string($itemName);
    $itemPrice = $conn->real_escape_string($itemPrice);
    $itemCategory = $conn->real_escape_string($itemCategory);
    $customerID = isset($_SESSION['customerID']) ? $conn->real_escape_string($_SESSION['customerID']) : '';
    $transactionID = isset($_SESSION['transactionID']) ? $conn->real_escape_string($_SESSION['transactionID']) : '';
    $itemNumber = $conn->real_escape_string($itemNumber);

    // Check if the item is already in the cart
    $cartCheckQuery = "SELECT * FROM Carts WHERE CustomerID = '$customerID' AND ItemNumber = '$itemNumber'";
    $cartResult = $conn->query($cartCheckQuery);

    if ($cartResult->num_rows > 0) {
        // Update the existing item in the cart
        $row = $cartResult->fetch_assoc();
        $newQuantity = $row['Quantity'] + $itemQuantity;
        $updateCartQuery = "UPDATE Carts SET Quantity = '$newQuantity' WHERE CustomerID = '$customerID' AND ItemNumber = '$itemNumber' AND CartStatus = 'pending'";
        $conn->query($updateCartQuery);
    } else {
        // Insert a new item into the cart
        $insertCartQuery = "INSERT INTO Carts (CustomerID, TransactionID, ItemNumber, Quantity, CartStatus) VALUES ('$customerID', '$transactionID', '$itemNumber', '$itemQuantity', 'pending')";
        $conn->query($insertCartQuery);
    }

    // Update the Inventory table if there is sufficient stock
    if ($currentInventoryQuantity >= $itemQuantity) {
        $newInventoryQuantity = $currentInventoryQuantity - $itemQuantity;
        $updateInventoryQuery = "UPDATE Inventory SET Quantity = '$newInventoryQuantity' WHERE ItemNumber = '$itemNumber'";
        $conn->query($updateInventoryQuery);
    } else {
        echo "Insufficient stock for item: $itemName";
    }
} else {
    echo "Item not found in inventory: $itemName";
}

$conn->close();

// Redirect back to the product page or you can show a success message
//header('Location: fresh_products.html');
// echo "Item added to cart successfully.";
?>
