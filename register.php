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
$full_name = $email = $password = $confirm_password = "";
$registration_errors = array();

// Process registration form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data and sanitize input
    $full_name = clean_input($_POST["full_name"]);
    $email = clean_input($_POST["email"]);
    $password = clean_input($_POST["password"]);
    $confirm_password = clean_input($_POST["confirm_password"]);

    // Validate form inputs
    if (empty($full_name)) {
        $registration_errors[] = "Full name is required.";
    }

    if (empty($email)) {
        $registration_errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $registration_errors[] = "Invalid email format.";
    }

    if (empty($password)) {
        $registration_errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $registration_errors[] = "Password must be at least 6 characters.";
    }

    if ($password !== $confirm_password) {
        $registration_errors[] = "Password and confirm password do not match.";
    }

    // If no errors, insert user into the database
    if (empty($registration_errors)) {
        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare and execute the SQL statement
        $sql = "INSERT INTO regi (full_name, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$full_name, $email, $hashed_password]);

        // Registration successful, redirect to a success page
        header("Location: login.php");
        exit();
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

<!-- Registration form HTML -->
<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
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

    input[type="text"],
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
    <h2>User Registration</h2>
    <?php if (!empty($registration_errors)): ?>
        <div style="color: red;">
            <ul>
                <?php foreach ($registration_errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="full_name">Full Name:</label><br>
        <input type="text" name="full_name" id="full_name" value="<?php echo $full_name; ?>"><br><br>
        <label for="email">Email:</label><br>
        <input type="email" name="email" id="email" value="<?php echo $email; ?>"><br><br>
        <label for="password">Password:</label><br>
        <input type="password" name="password" id="password"><br><br>
        <label for="confirm_password">Confirm Password:</label><br>
        <input type="password" name="confirm_password" id="confirm_password"><br><br>
        <input type="submit" value="Register">
    </form>
</body>
</html>
