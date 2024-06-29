<?php
// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get item name, removed quantity, and inventory source file from the POST data
    $itemName = isset($_POST['itemName']) ? $_POST['itemName'] : '';
    $removedQuantity = isset($_POST['removedQuantity']) ? (int)$_POST['removedQuantity'] : 0;
    $inventorySourceFile = isset($_POST['inventorySourceFile']) ? $_POST['inventorySourceFile'] : '';

    if (!empty($itemName) && $removedQuantity > 0 && !empty($inventorySourceFile)) {
        // Define the base path for inventory files
        $inventoryBasePath = 'path/to/your/inventories/';

        // Construct the path to the source inventory XML file
        $inventoryPath = $inventoryBasePath . $inventorySourceFile;

        // Read the XML file
        $xml = simplexml_load_file($inventoryPath);

        // Find the item in the XML and update its quantity
        foreach ($xml->item as $item) {
            if ((string)$item->name === $itemName) {
                $currentQuantity = (int)$item->quantity;
                $newQuantity = max(0, $currentQuantity - $removedQuantity);
                $item->quantity = $newQuantity;

                // Save the updated XML back to the file
                $xml->asXML($inventoryPath);

                // Respond with a success message
                echo json_encode(['success' => true, 'message' => 'Inventory updated successfully']);
                exit;
            }
        }

        // If the item was not found, respond with an error message
        http_response_code(404); // Not Found
        echo json_encode(['success' => false, 'error' => 'Item not found in the inventory']);
        exit;
    } else {
        // If the required parameters are missing or invalid, respond with a bad request error
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'error' => 'Invalid request parameters']);
        exit;
    }
} else {
    // Handle non-POST requests accordingly
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}
?>
