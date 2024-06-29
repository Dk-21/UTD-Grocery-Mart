<?php
// Update.php

// Get the data from the POST request
$itemName = isset($_POST['itemName']) ? $_POST['itemName'] : null;
$removedQuantity = isset($_POST['removedQuantity']) ? $_POST['removedQuantity'] : null;
$inventorySourceFile = isset($_POST['inventorySourceFile']) ? $_POST['inventorySourceFile'] : null;

// Validate the data
if ($itemName === null || $removedQuantity === null || $inventorySourceFile === null) {
    // Send a JSON error response to the client
    echo json_encode(['success' => false, 'error' => 'Invalid request data']);
    exit; // Terminate the script
}

// Set the Content-Type header to indicate JSON response
header('Content-Type: application/json');

// Handle XML file
if (pathinfo($inventorySourceFile, PATHINFO_EXTENSION) === 'xml') {
    $xml = simplexml_load_file($inventorySourceFile);

    // Check if loading the XML was successful
    if ($xml === false) {
        // Send a JSON error response to the client
        echo json_encode(['success' => false, 'error' => 'Failed to load XML file']);
        exit; // Terminate the script
    }

    // Flag to check if the product is found
    $productFound = false;

    // Iterate through each product in the XML file
    foreach ($xml->products->product as $product) {
        $productName = (string)$product['name'];

        // Convert both product name to lowercase for case-insensitive comparison
        $lowercaseName = strtolower($productName);

        if ($lowercaseName == strtolower($itemName)) {
            // Product found
            $productFound = true;

            // Update the quantity attribute (assuming it exists)
            $currentQuantity = intval($product['quantity']);
            $newQuantity = max(0, $currentQuantity - $removedQuantity); // Ensure the quantity is non-negative

            $product['quantity'] = $newQuantity;

            // Save the changes back to the XML file
            file_put_contents($inventorySourceFile, $xml->asXML());

            // Send a JSON response to the client
            echo json_encode(['success' => true, 'message' => 'Inventory updated successfully']);

            // Break the loop once the product is found
            break;
        }
    }

    // If the product is not found, send an error response to the client
    if (!$productFound) {
        echo json_encode(['success' => false, 'error' => 'Product not found in the inventory']);
    }
}
// Handle JSON file
elseif (pathinfo($inventorySourceFile, PATHINFO_EXTENSION) === 'json') {
    $jsonContent = file_get_contents($inventorySourceFile);
    $jsonData = json_decode($jsonContent, true);

    // Check if JSON decoding was successful
    if ($jsonData === null && json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['success' => false, 'error' => 'Failed to decode JSON file: ' . json_last_error_msg()]);
        exit; // Terminate the script
    }

    // Check if the expected structure is present in the decoded data
    if (!isset($jsonData['inventory']) || !is_array($jsonData['inventory'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid JSON structure']);
        exit;
    }

    // Flag to check if the product is found
    $productFound = false;

    // Iterate through each product in the data
    foreach ($jsonData['inventory'] as &$product) {
        // Convert both product name to lowercase for case-insensitive comparison
        $lowercaseName = strtolower($product['name']);

        if ($lowercaseName == strtolower($itemName)) {
            // Product found
            $productFound = true;

            // Update the quantity attribute (assuming it exists)
            $currentQuantity = intval($product['quantity']);
            $newQuantity = max(0, $currentQuantity - $removedQuantity); // Ensure the quantity is non-negative

            $product['quantity'] = $newQuantity;

            // Save the changes back to the file
            file_put_contents($inventorySourceFile, json_encode($jsonData, JSON_PRETTY_PRINT));

            // Send a JSON response to the client
            echo json_encode(['success' => true, 'message' => 'Inventory updated successfully']);

            // Break the loop once the product is found
            break;
        }
    }

    // If the product is not found, send an error response to the client
    if (!$productFound) {
        echo json_encode(['success' => false, 'error' => 'Product not found in the inventory']);
    }
}
// Unsupported file format
else {
    echo json_encode(['success' => false, 'error' => 'Unsupported file format']);
}
?>
