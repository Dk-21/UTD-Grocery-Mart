<!-- <?php
session_start();
$host = 'localhost';
$dbname = 'UTDMart';
$dbUsername = 'admin';
$dbPassword = 'password';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $dbUsername, $dbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $inputUsername = $_POST['login-username'];
        $inputPassword = $_POST['login-password'];

        // Retrieve user data from the database
        $stmt = $pdo->prepare("SELECT Password FROM Users WHERE UserName = ?");
        $stmt->execute([$inputUsername]);
        $user = $stmt->fetch();

        if ($user && password_verify($inputPassword, $user['Password'])) {
            // User authentication successful
            $_SESSION['username'] = $inputUsername;

            // Retrieve cart items from database if any
            $stmt = $pdo->prepare("SELECT ItemNumber, Quantity FROM Transactions WHERE CustomerID = ? AND Status = 'InCart'");
            $stmt->execute([$_SESSION['username']]);
            $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $_SESSION['cart'] = array();
            foreach ($cartItems as $item) {
                $_SESSION['cart'][$item['ItemNumber']] = $item['Quantity'];
            }

            header("Location: fresh_products.html");
            exit;
        } else {
            // User authentication failed
            echo "<script>alert('Invalid username or password.');</script>";
        }
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}
?>