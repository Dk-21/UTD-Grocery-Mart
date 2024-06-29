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

$xmlFile = $inventorySourceFile;
$xml = simplexml_load_file($xmlFile);

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

       
            $product['quantity'] = $currentQuantity + $removedQuantity;

            // Save the changes back to the XML file
            file_put_contents($xmlFile, $xml->asXML());

            // Send a response to the client (optional)
            echo 'Product quantity updated successfully';
       

        // Break the loop once the product is found
        break;
    }
}

// Add debugging information
echo 'Requested product: ' . $itemName . '<br>';
echo 'Lowercase requested product: ' . $lowercaseName . '<br>';
echo 'Products found: ' . ($productFound ? '1' : '0') . '<br>';
// echo 'XML content: <pre>' . htmlentities($xml->asXML()) . '</pre><br>';

// If the product is not found, send an error response to the client (optional)
if (!$productFound) {
    echo 'Error: Product not found';
}
?>