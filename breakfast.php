<?php
// update_cart.php

// Get the data from the AJAX request
$itemName = $_POST['itemName'];
$itemPrice = $_POST['itemPrice'];
$itemCategory = $_POST['itemCategory'];

// Read the JSON file
$jsonFile = 'breakfast_Inventory.json';
$jsonData = json_decode(file_get_contents($jsonFile), true);

// Check if JSON decoding was successful
if ($jsonData === null && json_last_error() !== JSON_ERROR_NONE) {
    echo 'Error decoding JSON: ' . json_last_error_msg();
    exit;
}

// Find the product in the JSON data
$productName = strtolower($itemName);

if (isset($jsonData['inventory'][$productName])) {
    // Product found, update the quantity
    $newQuantity = intval($jsonData['inventory'][$productName]) - 1;

    // Ensure the inventory is not less than 0
    if ($newQuantity >= 0) {
        $jsonData['inventory'][$productName] = $newQuantity;

        // Save the changes back to the JSON file
        file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT));

        // Send a response to the client (optional)
        echo 'Product added to cart successfully';
    } else {
        // Send an error response to the client
        echo 'Error: Insufficient inventory for ' . $itemName;
    }
} else {
    // Try finding the product without converting to lowercase
    if (isset($jsonData['inventory'][$itemName])) {
        // Product found, update the quantity
        $newQuantity = intval($jsonData['inventory'][$itemName]) - 1;

        // Ensure the inventory is not less than 0
        if ($newQuantity >= 0) {
            $jsonData['inventory'][$itemName] = $newQuantity;

            // Save the changes back to the JSON file
            file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT));

            // Send a response to the client (optional)
            echo 'Product added to cart successfully';
        } else {
            // Send an error response to the client
            echo 'Error: Insufficient inventory for ' . $itemName;
        }
    } else {
        // Send an error response to the client (optional)
        echo 'Error: Product not found';

        // Add debugging information
        echo 'Requested product: ' . $itemName . ' (' . $itemCategory . ')<br>';
        echo 'Available products: ' . implode(', ', array_keys($jsonData['inventory'])) . '<br>';
        echo 'JSON content: <pre>' . htmlentities(json_encode($jsonData, JSON_PRETTY_PRINT)) . '</pre><br>';
    }
}

// Add debugging information
echo 'Requested product: ' . $itemName . ' (' . $itemCategory . ')<br>';
if (isset($products)) {
    echo 'Products found: ' . count($products) . '<br>';
} else {
    echo 'Products found: 0<br>';
}
echo 'JSON content: <pre>' . htmlentities(json_encode($jsonData, JSON_PRETTY_PRINT)) . '</pre><br>';
?>
