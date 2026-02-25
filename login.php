<?php
session_start();

$host = 'localhost';
$dbname = 'inventory_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
} catch(PDOException $e) {
    die("Database error: " . $e->getMessage());
}


if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = "Please enter username and password";
    } else {
        // Find user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            
            // Go to home page
            header("Location: home.php");
            exit();
        } else {
            $error = "Wrong username or password";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Inventory System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background: #87CEEB;  /* Sky blue */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        
        .login-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
            width: 350px;
        }
        
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
        }
        
        .input-group {
            margin-bottom: 15px;
        }
        
        .input-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        
        .input-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .input-group input:focus {
            border-color: #4CAF50;
            outline: none;
        }
        
        button {
            width: 100%;
            padding: 12px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        
        button:hover {
            background: #45a049;
        }
        
        .error {
            background: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .test-info {
            margin-top: 20px;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .test-info p {
            margin: 5px 0;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>📦 Inventory System Login</h2>
        
        <?php if ($error): ?>
            <div class="error">❌ <?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="Enter username">
            </div>
            
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Enter password">
            </div>
            
            <button type="submit">Login</button>
        </form>
        
        <div class="test-info">
            <p><strong>🔑 Test Users (all password: 123)</strong></p>
            <p>• gaurav</p>
            <p>• manisha</p>
            <p style="color: #666; margin-top: 8px;">Use any username with password 123</p>
        </div>
    </div>
</body>
</html>