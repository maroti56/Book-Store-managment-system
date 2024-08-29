<?php
// Database connection settings
$host = "localhost";
$db_name = "register";
$username = "root";
$password = "";

// Establish database connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Define variables and set them empty initially
$email = $password = "";
$login_errors = array();

// Process login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data and sanitize input
    $email = clean_input($_POST["email"]);
    $password = clean_input($_POST["password"]);

    // Validate form inputs
    if (empty($email)) {
        $login_errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $login_errors[] = "Invalid email format.";
    }

    if (empty($password)) {
        $login_errors[] = "Password is required.";
    }

    // If no errors, attempt to authenticate user
    if (empty($login_errors)) {
        // Retrieve user record from the database based on the email
        $sql = "SELECT * FROM regi WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if a user with the given email exists and verify the password
        if ($user && password_verify($password, $user['password'])) {
            // Authentication successful, redirect to a success page
            header("Location: index.html");
            exit();
        } else {
            $login_errors[] = "Invalid email or password.";
        }
    }
}

// Function to sanitize input data
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>

<!-- Login form HTML -->
<!DOCTYPE html>
<html>
<head>
    <title>User Login</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #45a049;
    }

    h2 {
        text-align: center;
        margin-top: 20px;
    }

    form {
        width: 300px;
        margin: 0 auto;
    }

    label {
        display: block;
        margin-bottom: 5px;
    }

    input[type="email"],
    input[type="password"] {
        width: 100%;
        padding: 8px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }

    input[type="submit"] {
        background-color: #4CAF50;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        margin-top: 10px;
    }

    input[type="submit"]:hover {
        background-color: #45a049;
    }

    div.error {
        color: red;
        margin-bottom: 10px;
    }
</style>

</head>
<body>
    <h2>User Login</h2>
    <?php if (!empty($login_errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($login_errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="email">Email:</label><br>
        <input type="email" name="email" id="email" value="<?php echo $email; ?>"><br><br>
        <label for="password">Password:</label><br>
        <input type="password" name="password" id="password"><br><br>
        <a href="home.html">
        <input type="submit" value="Login">
        </a>
    </form>
</body>
</html>
