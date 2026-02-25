<?php
session_start();

// Simple check - if not logged in, go to login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Simple database connection
$host = 'localhost';
$dbname = 'inventory_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
} catch(PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Home - Inventory System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background: #f0f2f5;
        }
        
        .navbar {
            background: #333;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .nav-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .nav-left a {
            color: white;
            text-decoration: none;
            padding: 5px 10px;
        }
        
        .nav-left a:hover {
            background: #444;
            border-radius: 3px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
        }
        
        .container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .welcome-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .welcome-box h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .welcome-box p {
            color: #666;
        }
        
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .menu-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-decoration: none;
            color: #333;
        }
        
        .menu-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .menu-icon {
            font-size: 40px;
            margin-bottom: 10px;
        }
        
        .menu-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .menu-desc {
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="nav-left">
            <span style="font-weight: bold;">📦 Inventory System</span>
            <a href="home.php">Home</a>
            <a href="items/view_items.php">Items</a>
            <a href="requests/create_request.php">New Request</a>
            <a href="requests/my_requests.php">My Requests</a>
            <a href="reports/stock_report.php">Stock Report</a>
        </div>
        
        <div class="user-info">
            <span>👋 <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
            <a href="logout.php" class="logout-btn" onclick="return confirm('Logout?')">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="welcome-box">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
            <p>You are logged in as <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>
        </div>
        
        <h2 style="margin-bottom: 15px;">Quick Actions</h2>
        
        <div class="menu-grid">
            <a href="items/view_items.php" class="menu-card">
                <div class="menu-icon">📋</div>
                <div class="menu-title">View Items</div>
                <div class="menu-desc">See all items in stock</div>
            </a>
            
            <a href="requests/create_request.php" class="menu-card">
                <div class="menu-icon">➕</div>
                <div class="menu-title">Create Request</div>
                <div class="menu-desc">Request new items</div>
            </a>
            
            <a href="requests/my_requests.php" class="menu-card">
                <div class="menu-icon">📝</div>
                <div class="menu-title">My Requests</div>
                <div class="menu-desc">View your requests</div>
            </a>
            
            <a href="store/pending_requests.php" class="menu-card">
                <div class="menu-icon">⏳</div>
                <div class="menu-title">Pending Requests</div>
                <div class="menu-desc">View pending requests</div>
            </a>
            
            <a href="store/return_form.php" class="menu-card">
                <div class="menu-icon">↩️</div>
                <div class="menu-title">Return Items</div>
                <div class="menu-desc">Return issued items</div>
            </a>
            
            <a href="reports/stock_report.php" class="menu-card">
                <div class="menu-icon">📊</div>
                <div class="menu-title">Stock Report</div>
                <div class="menu-desc">View stock levels</div>
            </a>
            
            <a href="items/add_item.php" class="menu-card">
                <div class="menu-icon">➕</div>
                <div class="menu-title">Add Item</div>
                <div class="menu-desc">Add new item to inventory</div>
            </a>
        </div>
        
        <!-- Simple stats -->
        <div style="margin-top: 30px; background: white; padding: 20px; border-radius: 8px;">
            <h3>Quick Stats</h3>
            <div style="display: flex; gap: 20px; margin-top: 15px;">
                <?php
                $total_items = $pdo->query("SELECT COUNT(*) FROM items")->fetchColumn();
                $pending_reqs = $pdo->query("SELECT COUNT(*) FROM issue_requests WHERE status='pending'")->fetchColumn();
                ?>
                <div>📦 Total Items: <strong><?php echo $total_items; ?></strong></div>
                <div>⏳ Pending: <strong><?php echo $pending_reqs; ?></strong></div>
            </div>
        </div>
    </div>
</body>
</html>