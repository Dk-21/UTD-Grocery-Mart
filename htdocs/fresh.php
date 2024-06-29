<?php
// update_cart.php

// Get the data from the AJAX request
$itemName = $_POST['itemName'];
// $itemQuantity = $_POST['quantity'];

// Read the XML file
$xmlFile = 'fresh_inventory.xml';
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

        if ($currentQuantity >= 1) {
            $product['quantity'] = $currentQuantity - 1;

            // Save the changes back to the XML file
            file_put_contents($xmlFile, $xml->asXML());

            // Send a response to the client (optional)
            echo 'Product quantity updated successfully';
        } else {
            // Send an error response to the client (optional)
            echo 'Error: Insufficient quantity';
        }

        // Break the loop once the product is found
        break;
    }
}

// Add debugging information
echo 'Requested product: ' . $itemName . '<br>';
echo 'Lowercase requested product: ' . $lowercaseName . '<br>';
echo 'Products found: ' . ($productFound ? '1' : '0') . '<br>';
echo 'XML content: <pre>' . htmlentities($xml->asXML()) . '</pre><br>';

// If the product is not found, send an error response to the client (optional)
if (!$productFound) {
    echo 'Error: Product not found';
}
?>
