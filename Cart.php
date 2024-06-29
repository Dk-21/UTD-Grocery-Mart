<?php
session_start();

if (!isset($_SESSION['username']) || !isset($_SESSION['cart'])) {
    header("Location: login.php");
    exit;
}

echo "<h2>Your Cart</h2>";
foreach ($_SESSION['cart'] as $itemNumber => $quantity) {
    echo "Item: " . $itemNumber . ", Quantity: " . $quantity . "<br>";
}

echo "<a href='checkout.php'>Checkout</a>";
?>
