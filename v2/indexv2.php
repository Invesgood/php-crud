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
    <title>🚀 Ultra Modern CRUD | Kubernetes Ready</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
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
            --warning: linear-gradient(135deg, #fdbb2d 0%, #22c1c3 100%);
            --info: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            --dark: #1a202c;
            --light: #f7fafc;
            --white: #ffffff;
            --shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            --shadow-xl: 0 35px 60px -12px rgba(0, 0, 0, 0.25);
            --shadow-glow: 0 0 40px rgba(102, 126, 234, 0.4);
            --border-radius: 20px;
            --border-radius-xl: 30px;
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
            animation: backgroundShift 10s ease-in-out infinite alternate;
        }

        @keyframes backgroundShift {
            0% {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            }
            50% {
                background: linear-gradient(135deg, #f093fb 0%, #667eea 50%, #764ba2 100%);
            }
            100% {
                background: linear-gradient(135deg, #764ba2 0%, #f093fb 50%, #667eea 100%);
            }
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.4) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.4) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(120, 219, 255, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 60% 80%, rgba(255, 200, 124, 0.3) 0%, transparent 50%);
            pointer-events: none;
            z-index: -2;
            animation: floatingBubbles 15s ease-in-out infinite alternate;
        }

        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.05)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.05)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.03)"/><circle cx="10" cy="50" r="0.5" fill="rgba(255,255,255,0.03)"/><circle cx="90" cy="50" r="0.5" fill="rgba(255,255,255,0.03)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            pointer-events: none;
            z-index: -1;
            opacity: 0.3;
        }

        @keyframes floatingBubbles {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
            }
        }

        .container {
            max-width: 1600px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            animation: fadeInDown 1.2s ease-out;
        }

        .header h1 {
            font-size: 4rem;
            font-weight: 900;
            background: linear-gradient(135deg, #ffffff 0%, #f0f8ff 50%, #e6f3ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 15px;
            text-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            position: relative;
            display: inline-block;
        }

        .header h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            width: 100px;
            height: 4px;
            background: linear-gradient(135deg, #ff6b6b, #4ecdc4, #45b7d1);
            transform: translateX(-50%);
            border-radius: 2px;
            animation: rainbow 3s ease-in-out infinite;
        }

        @keyframes rainbow {
            0%, 100% { background: linear-gradient(135deg, #ff6b6b, #4ecdc4, #45b7d1); }
            33% { background: linear-gradient(135deg, #4ecdc4, #45b7d1, #ff6b6b); }
            66% { background: linear-gradient(135deg, #45b7d1, #ff6b6b, #4ecdc4); }
        }

        .header p {
            color: rgba(255, 255, 255, 0.95);
            font-size: 1.3rem;
            font-weight: 500;
            margin-bottom: 20px;
        }

        .header .subtitle {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .badge {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            padding: 8px 16px;
            border-radius: 50px;
            color: white;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            animation: float 3s ease-in-out infinite;
        }

        .badge:nth-child(2) { animation-delay: 0.5s; }
        .badge:nth-child(3) { animation-delay: 1s; }
        .badge:nth-child(4) { animation-delay: 1.5s; }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .health-status {
            background: var(--glass-bg);
            backdrop-filter: blur(30px);
            border: 1px solid var(--glass-border);
            border-radius: var(--border-radius-xl);
            padding: 30px;
            margin-bottom: 40px;
            animation: slideInUp 1s ease-out 0.3s both;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }

        .health-status::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #ff6b6b, #4ecdc4, #45b7d1, #ff6b6b);
            background-size: 300% 100%;
            animation: gradientMove 3s ease-in-out infinite;
        }

        @keyframes gradientMove {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .health-status h3 {
            color: white;
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .health-status h3 i {
            font-size: 1.5rem;
            background: var(--success);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: heartbeat 2s ease-in-out infinite;
        }

        @keyframes heartbeat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .status-item {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .status-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s;
        }

        .status-item:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-5px) scale(1.05);
            box-shadow: var(--shadow-glow);
        }

        .status-item:hover::before {
            left: 100%;
        }

        .status-item i {
            font-size: 2rem;
            margin-bottom: 12px;
            background: var(--success);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .status-item span {
            display: block;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .main-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 40px;
            align-items: start;
        }

        @media (max-width: 1200px) {
            .main-content {
                grid-template-columns: 1fr;
                gap: 30px;
            }
        }

        .form-section, .table-section {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(30px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: var(--border-radius-xl);
            padding: 40px;
            box-shadow: var(--shadow-xl);
            position: relative;
            overflow: hidden;
        }

        .form-section {
            animation: slideInLeft 1s ease-out 0.6s both;
            position: sticky;
            top: 20px;
        }

        .table-section {
            animation: slideInRight 1s ease-out 0.8s both;
        }

        .form-section::before, .table-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb, #667eea);
            background-size: 300% 100%;
            animation: gradientMove 4s ease-in-out infinite;
        }

        .section-title {
            color: var(--dark);
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            position: relative;
        }

        .section-title i {
            background: var(--primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 2.2rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 60px;
            height: 3px;
            background: var(--primary);
            border-radius: 2px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--dark);
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group label i {
            color: #667eea;
        }

        .form-group input {
            width: 100%;
            padding: 18px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-size: 1.1rem;
            transition: var(--transition);
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            font-weight: 500;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 1);
        }

        .form-group input:hover {
            border-color: #a0aec0;
            transform: translateY(-1px);
        }

        .btn {
            padding: 18px 35px;
            border: none;
            border-radius: 16px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
            letter-spacing: 0.5px;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 25px 50px rgba(102, 126, 234, 0.6);
        }

        .btn-success {
            background: var(--success);
            color: white;
            box-shadow: 0 15px 35px rgba(79, 172, 254, 0.4);
        }

        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 25px 50px rgba(79, 172, 254, 0.6);
        }

        .btn-danger {
            background: var(--danger);
            color: white;
            box-shadow: 0 15px 35px rgba(250, 112, 154, 0.4);
        }

        .btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 25px 50px rgba(250, 112, 154, 0.6);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #718096 0%, #4a5568 100%);
            color: white;
            box-shadow: 0 15px 35px rgba(113, 128, 150, 0.4);
        }

        .btn-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 25px 50px rgba(113, 128, 150, 0.6);
        }

        .btn-small {
            padding: 12px 20px;
            font-size: 0.95rem;
        }

        .button-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .table-container {
            overflow: hidden;
            border-radius: 20px;
            box-shadow: var(--shadow);
            background: white;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        thead th {
            padding: 25px 20px;
            color: white;
            font-weight: 700;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            text-align: left;
        }

        thead th:first-child {
            border-top-left-radius: 20px;
        }

        thead th:last-child {
            border-top-right-radius: 20px;
        }

        tbody tr {
            transition: var(--transition);
            border-bottom: 1px solid #f1f5f9;
            position: relative;
        }

        tbody tr::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 100%;
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            transition: width 0.3s ease;
            z-index: -1;
        }

        tbody tr:hover::before {
            width: 100%;
        }

        tbody tr:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        tbody td {
            padding: 25px 20px;
            color: var(--dark);
            font-weight: 500;
            font-size: 1rem;
            position: relative;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.2rem;
            margin-right: 15px;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-details h4 {
            margin: 0 0 5px 0;
            color: var(--dark);
            font-weight: 700;
            font-size: 1.1rem;
        }

        .user-details p {
            margin: 0;
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .actions {
            display: flex;
            gap: 12px;
        }

        .user-id {
            font-weight: 800;
            font-size: 1.1rem;
            color: #667eea;
        }

        .contact-info {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }

        .contact-info i {
            color: #667eea;
            font-size: 1.1rem;
        }

        .message {
            position: fixed;
            top: 30px;
            right: 30px;
            padding: 20px 30px;
            border-radius: 16px;
            color: white;
            font-weight: 700;
            z-index: 10000;
            animation: slideInRight 0.6s ease-out;
            box-shadow: var(--shadow-xl);
            backdrop-filter: blur(20px);
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 300px;
        }

        .message.success {
            background: var(--success);
        }

        .message.error {
            background: var(--danger);
        }

        .message i {
            font-size: 1.3rem;
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #64748b;
        }

        .empty-state i {
            font-size: 5rem;
            margin-bottom: 30px;
            opacity: 0.6;
            background: var(--secondary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .empty-state h3 {
            font-size: 2rem;
            margin-bottom: 15px;
            color: var(--dark);
            font-weight: 700;
        }

        .empty-state p {
            font-size: 1.1rem;
            color: #64748b;
        }

        .floating-particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .particle:nth-child(1) { left: 10%; animation-delay: 0s; }
        .particle:nth-child(2) { left: 20%; animation-delay: 1s; }
        .particle:nth-child(3) { left: 30%; animation-delay: 2s; }
        .particle:nth-child(4) { left: 40%; animation-delay: 3s; }
        .particle:nth-child(5) { left: 50%; animation-delay: 4s; }
        .particle:nth-child(6) { left: 60%; animation-delay: 5s; }
        .particle:nth-child(7) { left: 70%; animation-delay: 0.5s; }
        .particle:nth-child(8) { left: 80%; animation-delay: 1.5s; }
        .particle:nth-child(9) { left: 90%; animation-delay: 2.5s; }

        .stats-counter {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 12px 20px;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .loading-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #667eea;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Enhanced Keyframes */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px) rotate(-5deg);
            }
            to {
                opacity: 1;
                transform: translateX(0) rotate(0deg);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px) rotate(5deg);
            }
            to {
                opacity: 1;
                transform: translateX(0) rotate(0deg);
            }
        }

        /* Mobile Responsive Enhancements */
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }

            .header h1 {
                font-size: 2.5rem;
            }

            .form-section, .table-section {
                padding: 25px;
            }

            .status-grid {
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                gap: 15px;
            }

            .table-container {
                overflow-x: auto;
            }

            .message {
                top: 15px;
                right: 15px;
                left: 15px;
                min-width: auto;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .form-section, .table-section {
                background: rgba(26, 32, 44, 0.95);
                border: 1px solid rgba(255, 255, 255, 0.1);
            }

            .section-title {
                color: white;
            }

            .form-group label {
                color: #e2e8f0;
            }

            .form-group input {
                background: rgba(45, 55, 72, 0.8);
                border-color: #4a5568;
                color: white;
            }

            .form-group input:focus {
                background: rgba(45, 55, 72, 1);
                border-color: #667eea;
            }

            tbody td {
                color: #e2e8f0;
            }

            table {
                background: #2d3748;
            }

            tbody tr {
                border-bottom-color: #4a5568;
            }
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #764ba2, #f093fb);
        }
    </style>
</head>
<body>
    <!-- Floating Particles -->
    <div class="floating-particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <div class="container">
        <!-- Enhanced Header -->
        <div class="header">
            <h1><i class="fas fa-rocket"></i> Ultra Modern CRUD</h1>
            <p>Next-Generation Kubernetes-Ready PHP Application</p>
            <div class="subtitle">
                <div class="badge">
                    <i class="fas fa-shield-alt"></i>
                    Enterprise Ready
                </div>
                <div class="badge">
                    <i class="fas fa-bolt"></i>
                    Lightning Fast
                </div>
                <div class="badge">
                    <i class="fab fa-docker"></i>
                    Containerized
                </div>
                <div class="badge">
                    <i class="fas fa-mobile-alt"></i>
                    Responsive
                </div>
            </div>
        </div>

        <!-- Enhanced Health Status -->
        <div class="health-status">
            <h3><i class="fas fa-heartbeat"></i> System Health Dashboard</h3>
            <div class="status-grid">
                <div class="status-item">
                    <i class="fas fa-database"></i>
                    <span><?php echo $db ? '✅ Database Online' : '❌ Database Error'; ?></span>
                </div>
                <div class="status-item">
                    <i class="fas fa-server"></i>
                    <span>🚀 PHP <?php echo phpversion(); ?></span>
                </div>
                <div class="status-item">
                    <i class="fas fa-users"></i>
                    <span>👥 <?php echo count($users); ?> Active Users</span>
                </div>
                <div class="status-item">
                    <i class="fas fa-cloud"></i>
                    <span>☁️ Cloud Native</span>
                </div>
                <div class="status-item">
                    <i class="fas fa-chart-line"></i>
                    <span>📈 Performance Optimized</span>
                </div>
                <div class="status-item">
                    <i class="fas fa-lock"></i>
                    <span>🔒 Secure by Design</span>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if(isset($_SESSION['message'])): ?>
            <div class="message <?php echo $_SESSION['message_type'] ?? 'success'; ?>">
                <i class="fas fa-check-circle"></i>
                <span><?php echo $_SESSION['message']; unset($_SESSION['message'], $_SESSION['message_type']); ?></span>
            </div>
            <script>
                setTimeout(() => {
                    document.querySelector('.message').style.animation = 'slideInRight 0.6s ease-out reverse';
                    setTimeout(() => document.querySelector('.message')?.remove(), 600);
                }, 4000);
            </script>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="message error">
                <i class="fas fa-exclamation-triangle"></i>
                <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
            </div>
            <script>
                setTimeout(() => {
                    document.querySelector('.message').style.animation = 'slideInRight 0.6s ease-out reverse';
                    setTimeout(() => document.querySelector('.message')?.remove(), 600);
                }, 4000);
            </script>
        <?php endif; ?>

        <div class="main-content">
            <!-- Enhanced Form Section -->
            <div class="form-section">
                <h2 class="section-title">
                    <i class="fas fa-<?php echo $editUser ? 'user-edit' : 'user-plus'; ?>"></i>
                    <?php echo $editUser ? 'Edit User Profile' : 'Create New User'; ?>
                </h2>
                
                <form method="POST" id="userForm">
                    <?php if($editUser): ?>
                        <input type="hidden" name="id" value="<?php echo $_GET['edit']; ?>">
                        <input type="hidden" name="action" value="update">
                    <?php else: ?>
                        <input type="hidden" name="action" value="create">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="name">
                            <i class="fas fa-user"></i>
                            Full Name
                        </label>
                        <input type="text" id="name" name="name" required 
                               placeholder="Enter your full name..."
                               value="<?php echo $editUser ? htmlspecialchars($editUser->name) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-at"></i>
                            Email Address
                        </label>
                        <input type="email" id="email" name="email" required 
                               placeholder="Enter your email address..."
                               value="<?php echo $editUser ? htmlspecialchars($editUser->email) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">
                            <i class="fas fa-mobile-alt"></i>
                            Phone Number
                        </label>
                        <input type="text" id="phone" name="phone" required 
                               placeholder="Enter your phone number..."
                               value="<?php echo $editUser ? htmlspecialchars($editUser->phone) : ''; ?>">
                    </div>
                    
                    <div class="button-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-<?php echo $editUser ? 'save' : 'plus'; ?>"></i>
                            <span><?php echo $editUser ? 'Update User' : 'Create User'; ?></span>
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

            <!-- Enhanced Table Section -->
            <div class="table-section">
                <h2 class="section-title">
                    <i class="fas fa-users"></i>
                    Users Management
                    <div class="stats-counter">
                        <i class="fas fa-chart-bar"></i>
                        <?php echo count($users); ?> total records
                    </div>
                </h2>
                
                <?php if(count($users) > 0): ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th><i class="fas fa-hashtag"></i> ID</th>
                                    <th><i class="fas fa-user"></i> User Profile</th>
                                    <th><i class="fas fa-envelope"></i> Email</th>
                                    <th><i class="fas fa-phone"></i> Phone</th>
                                    <th><i class="fas fa-tools"></i> Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($users as $user_item): ?>
                                    <tr>
                                        <td>
                                            <div class="user-id">#<?php echo htmlspecialchars($user_item['id']); ?></div>
                                        </td>
                                        <td>
                                            <div class="user-info">
                                                <div class="user-avatar">
                                                    <?php echo strtoupper(substr($user_item['name'], 0, 2)); ?>
                                                </div>
                                                <div class="user-details">
                                                    <h4><?php echo htmlspecialchars($user_item['name']); ?></h4>
                                                    <p>Active User</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="contact-info">
                                                <i class="fas fa-at"></i>
                                                <?php echo htmlspecialchars($user_item['email']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="contact-info">
                                                <i class="fas fa-phone"></i>
                                                <?php echo htmlspecialchars($user_item['phone']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="actions">
                                                <a href="?edit=<?php echo $user_item['id']; ?>" 
                                                   class="btn btn-success btn-small"
                                                   title="Edit User">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <form style="display:inline;" method="POST" 
                                                      onsubmit="return confirmDelete('<?php echo htmlspecialchars($user_item['name']); ?>')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $user_item['id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-small"
                                                            title="Delete User">
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
                        <i class="fas fa-user-friends"></i>
                        <h3>No Users Found</h3>
                        <p>Start building your user base by creating your first user profile.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Enhanced form submission with loading
        document.getElementById('userForm').addEventListener('submit', function(e) {
            const loadingOverlay = document.getElementById('loadingOverlay');
            loadingOverlay.classList.add('active');
            
            // Add a slight delay for better UX
            setTimeout(() => {
                this.submit();
            }, 500);
            
            e.preventDefault();
        });

        // Enhanced delete confirmation
        function confirmDelete(userName) {
            return confirm(`🗑️ Delete User: ${userName}\n\n⚠️ This action cannot be undone!\n\nAre you sure you want to proceed?`);
        }

        // Auto-hide messages
        document.addEventListener('DOMContentLoaded', function() {
            const messages = document.querySelectorAll('.message');
            messages.forEach(message => {
                setTimeout(() => {
                    message.style.transform = 'translateX(400px)';
                    message.style.opacity = '0';
                    setTimeout(() => message.remove(), 300);
                }, 5000);
            });
        });

        // Add subtle animations to form inputs
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        // Enhanced table row interactions
        const tableRows = document.querySelectorAll('tbody tr');
        tableRows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.02) translateZ(10px)';
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1) translateZ(0px)';
            });
        });
    </script>
</body>
</html>