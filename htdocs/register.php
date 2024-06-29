<?php
$host = 'localhost';
$dbname = 'UTDMart';
$username = 'admin';
$password = 'password';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $username = $_POST['username'];
        $password = $_POST['password']; // Consider hashing the password
        $firstName = $_POST['firstname'];
        $lastName = $_POST['lastname'];
        $dob = $_POST['dob'];
        $email = $_POST['email'];
        $address = $_POST['address'];
        $phoneNumber = $_POST['phone']; // Added phone number

        // Calculate age from the date of birth
        $birthDate = new DateTime($dob);
        $currentDate = new DateTime();
        $age = $currentDate->diff($birthDate)->y;

        // Generate a unique Customer ID
        $customerID = uniqid('cust_');

        // Start transaction
        $pdo->beginTransaction();

        // Insert customer data into the Customers table
        $stmtCustomers = $pdo->prepare("INSERT INTO Customers (CustomerID, FirstName, LastName, Age, PhoneNumber, Email, Address) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmtCustomers->execute([$customerID, $firstName, $lastName, $age, $phoneNumber, $email, $address]);

        // Hash password for security
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert user data into the Users table
        $stmtUsers = $pdo->prepare("INSERT INTO Users (CustomerID, UserName, Password) VALUES (?, ?, ?)");
        $stmtUsers->execute([$customerID, $username, $hashedPassword]);

        // Commit transaction
        $pdo->commit();

        // Notify the user of successful registration
        echo "<script>alert('Registered successfully! Your customer ID is: " . $customerID . "');</script>";

    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Database error: " . $e->getMessage());
    }
}
?>
