<?php
// config.php - Database Configuration
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->db_name = $_ENV['DB_NAME'] ?? 'crud_app';
        $this->username = $_ENV['DB_USER'] ?? 'root';
        $this->password = $_ENV['DB_PASS'] ?? '';
    }

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

// User Model
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $name;
    public $email;
    public $phone;

    public function __construct($db) {
        $this->conn = $db;
    }

    function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 SET name=:name, email=:email, phone=:phone";

        $stmt = $this->conn->prepare($query);
        
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    function readAll() {
        $query = "SELECT id, name, email, phone FROM " . $this->table_name . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function readOne() {
        $query = "SELECT name, email, phone FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->name = $row['name'];
            $this->email = $row['email'];
            $this->phone = $row['phone'];
        }
    }

    function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET name=:name, email=:email, phone=:phone 
                 WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}

// Main Application Logic
session_start();

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

// Handle form submissions
if($_POST) {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'create':
                $user->name = $_POST['name'];
                $user->email = $_POST['email'];
                $user->phone = $_POST['phone'];
                
                if($user->create()) {
                    $_SESSION['message'] = "User created successfully!";
                    $_SESSION['message_type'] = "success";
                } else {
                    $_SESSION['error'] = "Unable to create user.";
                    $_SESSION['message_type'] = "error";
                }
                break;
                
            case 'update':
                $user->id = $_POST['id'];
                $user->name = $_POST['name'];
                $user->email = $_POST['email'];
                $user->phone = $_POST['phone'];
                
                if($user->update()) {
                    $_SESSION['message'] = "User updated successfully!";
                    $_SESSION['message_type'] = "success";
                } else {
                    $_SESSION['error'] = "Unable to update user.";
                    $_SESSION['message_type'] = "error";
                }
                break;
                
            case 'delete':
                $user->id = $_POST['id'];
                if($user->delete()) {
                    $_SESSION['message'] = "User deleted successfully!";
                    $_SESSION['message_type'] = "success";
                } else {
                    $_SESSION['error'] = "Unable to delete user.";
                    $_SESSION['message_type'] = "error";
                }
                break;
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Get user for editing
$editUser = null;
if(isset($_GET['edit'])) {
    $user->id = $_GET['edit'];
    $user->readOne();
    $editUser = $user;
}

// Get all users
$stmt = $user->readAll();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern CRUD Application | Kubernetes Ready</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --danger: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --dark: #2d3748;
            --light: #f7fafc;
            --white: #ffffff;
            --shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --shadow-lg: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            --border-radius: 16px;
            --border-radius-lg: 24px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(120, 219, 255, 0.3) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            animation: fadeInDown 1s ease-out;
        }

        .header h1 {
            font-size: 3.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #ffffff 0%, #f0f8ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.2rem;
            font-weight: 500;
        }

        .health-status {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 30px;
            animation: slideInUp 1s ease-out 0.2s both;
        }

        .health-status h3 {
            color: white;
            font-size: 1.1rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .status-item {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            transition: var(--transition);
        }

        .status-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .status-item i {
            font-size: 1.5rem;
            margin-bottom: 8px;
            color: #4ade80;
        }

        .status-item span {
            display: block;
            color: white;
            font-weight: 600;
        }

        .main-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
            align-items: start;
        }

        @media (max-width: 1024px) {
            .main-content {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }

        .form-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--border-radius-lg);
            padding: 30px;
            box-shadow: var(--shadow-lg);
            animation: slideInLeft 1s ease-out 0.4s both;
            position: sticky;
            top: 20px;
        }

        .form-section h2 {
            color: var(--dark);
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .form-section h2 i {
            background: var(--primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
            font-size: 0.95rem;
        }

        .form-group input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: var(--transition);
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-1px);
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.4);
        }

        .btn-success {
            background: var(--success);
            color: white;
            box-shadow: 0 10px 25px rgba(79, 172, 254, 0.3);
        }

        .btn-danger {
            background: var(--danger);
            color: white;
            box-shadow: 0 10px 25px rgba(250, 112, 154, 0.3);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #718096 0%, #4a5568 100%);
            color: white;
        }

        .btn-small {
            padding: 8px 16px;
            font-size: 0.875rem;
        }

        .table-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--border-radius-lg);
            padding: 30px;
            box-shadow: var(--shadow-lg);
            animation: slideInRight 1s ease-out 0.6s both;
        }

        .table-section h2 {
            color: var(--dark);
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .table-section h2 i {
            background: var(--secondary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .table-container {
            overflow-x: auto;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        thead th {
            padding: 20px;
            color: white;
            font-weight: 600;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
        }

        tbody tr {
            transition: var(--transition);
            border-bottom: 1px solid #e2e8f0;
        }

        tbody tr:hover {
            background: linear-gradient(135deg, #f8faff 0%, #f0f8ff 100%);
            transform: scale(1.01);
        }

        tbody td {
            padding: 20px;
            color: var(--dark);
            font-weight: 500;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            margin-right: 10px;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-details h4 {
            margin: 0;
            color: var(--dark);
            font-weight: 600;
        }

        .user-details p {
            margin: 0;
            color: #718096;
            font-size: 0.875rem;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            z-index: 1000;
            animation: slideInRight 0.5s ease-out;
            box-shadow: var(--shadow);
        }

        .message.success {
            background: var(--success);
        }

        .message.error {
            background: var(--danger);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #718096;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: var(--dark);
        }

        .floating-action {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            transition: var(--transition);
            z-index: 100;
        }

        .floating-action:hover {
            transform: scale(1.1) rotate(90deg);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.6);
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .header h1 {
                font-size: 2.5rem;
            }

            .form-section, .table-section {
                padding: 20px;
            }

            .table-container {
                overflow-x: scroll;
            }

            .floating-action {
                bottom: 20px;
                right: 20px;
            }
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-rocket"></i> Modern CRUD</h1>
            <p>Kubernetes-Ready PHP Application with Stunning UI</p>
        </div>

        <!-- Health Status -->
        <div class="health-status">
            <h3><i class="fas fa-heart pulse"></i> System Health Check</h3>
            <div class="status-grid">
                <div class="status-item">
                    <i class="fas fa-database"></i>
                    <span><?php echo $db ? 'Database Connected' : 'Database Error'; ?></span>
                </div>
                <div class="status-item">
                    <i class="fas fa-server"></i>
                    <span>PHP <?php echo phpversion(); ?></span>
                </div>
                <div class="status-item">
                    <i class="fas fa-users"></i>
                    <span><?php echo count($users); ?> Total Users</span>
                </div>
                <div class="status-item">
                    <i class="fas fa-cloud"></i>
                    <span>Kubernetes Ready</span>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if(isset($_SESSION['message'])): ?>
            <div class="message <?php echo $_SESSION['message_type'] ?? 'success'; ?>">
                <i class="fas fa-check-circle"></i>
                <?php echo $_SESSION['message']; unset($_SESSION['message'], $_SESSION['message_type']); ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="message error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="main-content">
            <!-- Form Section -->
            <div class="form-section">
                <h2>
                    <i class="fas fa-<?php echo $editUser ? 'edit' : 'plus'; ?>"></i>
                    <?php echo $editUser ? 'Edit User' : 'Add New User'; ?>
                </h2>
                
                <form method="POST" id="userForm">
                    <?php if($editUser): ?>
                        <input type="hidden" name="id" value="<?php echo $_GET['edit']; ?>">
                        <input type="hidden" name="action" value="update">
                    <?php else: ?>
                        <input type="hidden" name="action" value="create">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="name"><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" id="name" name="name" required 
                               placeholder="Enter full name..."
                               value="<?php echo $editUser ? htmlspecialchars($editUser->name) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                        <input type="email" id="email" name="email" required 
                               placeholder="Enter email address..."
                               value="<?php echo $editUser ? htmlspecialchars($editUser->email) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone"><i class="fas fa-phone"></i> Phone Number</label>
                        <input type="text" id="phone" name="phone" required 
                               placeholder="Enter phone number..."
                               value="<?php echo $editUser ? htmlspecialchars($editUser->phone) : ''; ?>">
                    </div>
                    
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-<?php echo $editUser ? 'save' : 'plus'; ?>"></i>
                            <span><?php echo $editUser ? 'Update User' : 'Add User'; ?></span>
                        </button>
                        
                        <?php if($editUser): ?>
                            <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                Cancel
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Table Section -->
            <div class="table-section">
                <h2>
                    <i class="fas fa-users"></i>
                    Users Directory
                    <span style="margin-left: auto; font-size: 1rem; font-weight: normal; color: #718096;">
                        <?php echo count($users); ?> users found
                    </span>
                </h2>
                
                <?php if(count($users) > 0): ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th><i class="fas fa-hashtag"></i> ID</th>
                                    <th><i class="fas fa-user"></i> User Info</th>
                                    <th><i class="fas fa-envelope"></i> Email</th>
                                    <th><i class="fas fa-phone"></i> Phone</th>
                                    <th><i class="fas fa-cogs"></i> Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($users as $user_item): ?>
                                    <tr>
                                        <td>
                                            <strong>#<?php echo htmlspecialchars($user_item['id']); ?></strong>
                                        </td>
                                        <td>
                                            <div class="user-info">
                                                <div class="user-avatar">
                                                    <?php echo strtoupper(substr($user_item['name'], 0, 1)); ?>
                                                </div>
                                                <div class="user-details">
                                                    <h4><?php echo htmlspecialchars($user_item['name']); ?></h4>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <i class="fas fa-envelope" style="color: #667eea; margin-right: 8px;"></i>
                                            <?php echo htmlspecialchars($user_item['email']); ?>
                                        </td>
                                        <td>
                                            <i class="fas fa-phone" style="color: #48bb78; margin-right: 8px;"></i>
                                            <?php echo htmlspecialchars($user_item['phone']); ?>
                                        </td>
                                        <td>
                                            <div class="actions">
                                                <a href="?edit=<?php echo $user_item['id']; ?>" 
                                                   class="btn btn-success btn-small">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <form style="display:inline;" method="POST" 
                                                      onsubmit="return confirm('🗑️ Are you sure you want to delete this user?\n\nThis action cannot be undone!')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $user_item['id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-small">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <h3>No Users Found</h3>
                        <p>Start by adding your first user using the form on the left.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!--