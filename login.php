<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$host = 'localhost';
$dbname = 'UTDMart';
$db_username = 'admin'; // Ensure this is your database username
$db_password = 'password'; // Ensure this is your database password
$temp = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $db_username, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $loginStatus = "";

    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $form_username = $_GET['username'];
        $form_password = $_GET['password'];

        $stmt = $pdo->prepare("SELECT CustomerID, Password FROM Users WHERE UserName = ?");
        $stmt->bindParam(1, $form_username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($form_password, $row['Password'])) {
                $_SESSION['loggedin'] = true;
                $_SESSION['customerID'] = $row['CustomerID'];
                $loginStatus = "Login successful.";
                $temp = $_SESSION['customerID'];
                header("Location: Fresh_Products.html");
            } else {
                $loginStatus = "Invalid username or password.";
            }
        } else {
            $loginStatus = "Invalid username or password.";
        }
    }
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$pdo = null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Status</title>
</head>
<body>
    <?php
    if (!empty($loginStatus)) {
        echo "<p>$loginStatus</p>";
    }
    ?>
</body>
</html>
