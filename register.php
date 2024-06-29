<?php
session_start();
$host = 'localhost';
$dbname = 'UTDMart';
$username = 'admin';
$password = 'password';

function generateTransactionID() {
    return bin2hex(random_bytes(10)); // Generates a unique transaction ID
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Retrieve form data
        $formUsername = $_POST['username'];
        $formPassword = $_POST['password']; // This will be hashed for security
        $firstName = $_POST['firstname'];
        $lastName = $_POST['lastname'];
        $dob = $_POST['dob'];
        $email = $_POST['email'];
        $address = $_POST['address'];
        $phoneNumber = $_POST['phone'];

        // Calculate age from date of birth
        $birthDate = new DateTime($dob);
        $currentDate = new DateTime();
        $age = $currentDate->diff($birthDate)->y;

        // Generate a unique Customer ID
        $customerID = uniqid('cust_');

        // Begin transaction
        $pdo->beginTransaction();

        // Insert data into Customers table
        $stmtCustomers = $pdo->prepare("INSERT INTO Customers (CustomerID, FirstName, LastName, Age, PhoneNumber, Email, Address) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmtCustomers->execute([$customerID, $firstName, $lastName, $age, $phoneNumber, $email, $address]);

        // Hash the password for security
        $hashedPassword = password_hash($formPassword, PASSWORD_DEFAULT);

        // Insert data into Users table
        $stmtUsers = $pdo->prepare("INSERT INTO Users (CustomerID, UserName, Password) VALUES (?, ?, ?)");
        $stmtUsers->execute([$customerID, $formUsername, $hashedPassword]);

        // Generate a unique Transaction ID for the Cart
        $transactionID = generateTransactionID();

        // Insert a new entry in the Cart table with default values
        $stmtCart = $pdo->prepare("INSERT INTO Carts (TransactionID, CustomerID, ItemNumber, Quantity) VALUES (?, ?, 0, 0)");
        $stmtCart->execute([$transactionID, $customerID]);

        // Commit the transaction
        $pdo->commit();

        // Store Customer ID and Transaction ID in session
        $_SESSION['customerID'] = $customerID;
        $_SESSION['transactionID'] = $transactionID;

        // Redirect to fresh_products.html
        header("Location: Fresh_Products.html");
        exit;

    } catch (PDOException $e) {
        // Roll back the transaction in case of an error
        $pdo->rollBack();
        die("Database error: " . $e->getMessage());
    }
}
?>
