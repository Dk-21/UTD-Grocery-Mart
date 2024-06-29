<?php

// Get the JSON data from the request body
$jsonData = file_get_contents('php://input');

// Path to the JSON file
// $jsonFilePath = 'path/to/inventory.json';
$jsonFilePath = __DIR__ . '/Pantry_Inventory.json';


// Load the existing JSON file
$inventory = json_decode(file_get_contents($jsonFilePath), true);

// Load the new JSON data
$newData = json_decode($jsonData, true);

// Update the inventory based on the new data
foreach ($newData as $newItem) {
    $itemName = $newItem['name'];
    $itemQuantity = $newItem['quantity'];

    // Update the quantity if the item exists
    if (isset($inventory[$itemName])) {
        $inventory[$itemName] -= $itemQuantity;
    } else {
        // Handle case where the item is not found (optional)
        // You may choose to add the item to the inventory here
    }
}

// Save the updated JSON back to the file
file_put_contents($jsonFilePath, json_encode($inventory));

// Respond with a success message
$response = ['status' => 'success', 'message' => 'Inventory updated successfully'];
echo json_encode($response);

?>
