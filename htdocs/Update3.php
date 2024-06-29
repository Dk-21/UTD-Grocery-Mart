<?php
// Update.php

// Get the data from the POST request
$itemName = isset($_POST['itemName']) ? $_POST['itemName'] : null;
$removedQuantity = isset($_POST['removedQuantity']) ? $_POST['removedQuantity'] : null;
$inventorySourceFile = isset($_POST['inventorySourceFile']) ? $_POST['inventorySourceFile'] : null;


echo $inventorySourceFile;
echo $itemName;
echo $removedQuantity;

// Validate the data
if ($itemName === null || $removedQuantity === null || $inventorySourceFile === null) {
    // Send a JSON error response to the client
    echo json_encode(['success' => false, 'error' => 'Invalid request data']);
    exit; // Terminate the script
}

// Set the Content-Type header to indicate JSON response
header('Content-Type: application/json');

// Ensure the $inventorySourceFile is a valid file path
$allowedFiles = ['file1.xml', 'file2.xml']; // Add your allowed XML files
if (!in_array($inventorySourceFile, $allowedFiles)) {
    // Send a JSON error response to the client
    echo json_encode(['success' => false, 'error' => 'Invalid inventory source file']);
    exit; // Terminate the script
}

// Read the XML file
$xml = simplexml_load_file($inventorySourceFile);

// Check if loading the XML was successful
if ($xml === false) {
    // Send a JSON error response to the client
    echo json_encode(['success' => false, 'error' => 'Failed to load XML file']);
    exit; // Terminate the script
}

// Find the product in the XML file
$products = $xml->xpath("//product[name='{$itemName}']");

// Check if the product is found
if (!empty($products)) {
    // Get the first product in the array
    $product = $products[0];

    // Update the quantity attribute (assuming it exists)
    $currentQuantity = intval($product['quantity']);
    $newQuantity = max(0, $currentQuantity - $removedQuantity); // Ensure the quantity is non-negative

    $product['quantity'] = $newQuantity;

    // Save the changes back to the XML file
    file_put_contents($inventorySourceFile, $xml->asXML());

    // Send a JSON response to the client
    echo json_encode(['success' => true, 'message' => 'Inventory updated successfully']);
} else {
    // Send a JSON error response to the client
    echo json_encode(['success' => false, 'error' => 'Product not found in the inventory']);
}
?>
