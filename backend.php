<?php
// LoveNet Backend API
// Handle user registration, login, and data storage

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Headers: Content-Type, multipart/form-data');

// Database configuration
$host = 'localhost';
$dbname = 'lovenet_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    exit(0);
}

// Get request data
$input = json_decode(file_get_contents('php://input'), true);

switch($method) {
    case 'POST':
        $action = $_GET['action'] ?? '';
        
        switch($action) {
            case 'register':
                handleRegistration($pdo, $input);
                break;
            case 'login':
                handleLogin($pdo, $input);
                break;
            case 'search':
                handleSearch($pdo, $input);
                break;
            default:
                echo json_encode(['error' => 'Invalid action']);
        }
        break;
        
    case 'GET':
        $action = $_GET['action'] ?? '';
        
        switch($action) {
            case 'users':
                getUsers($pdo);
                break;
            case 'profiles':
                getProfiles($pdo);
                break;
            default:
                echo json_encode(['error' => 'Invalid action']);
        }
        break;
        
    default:
        echo json_encode(['error' => 'Method not allowed']);
}

function handleRegistration($pdo, $data) {
    try {
        // Handle image upload
        $profilePicPath = null;
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $file = $_FILES['profile_pic'];
            $fileName = time() . '_' . basename($file['name']);
            $targetPath = $uploadDir . $fileName;
            
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file['type'], $allowedTypes)) {
                echo json_encode(['error' => 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed']);
                return;
            }
            
            // Validate file size (max 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                echo json_encode(['error' => 'File size must be less than 5MB']);
                return;
            }
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $profilePicPath = $targetPath;
            } else {
                echo json_encode(['error' => 'Failed to upload image']);
                return;
            }
        }
        
        // Validate required fields
        $required = ['username', 'email', 'password', 'gender', 'dob'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                echo json_encode(['error' => "Field '$field' is required"]);
                return;
            }
        }
        
        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['error' => 'Invalid email format']);
            return;
        }
        
        // Validate age (must be 18+)
        $birthDate = new DateTime($data['dob']);
        $today = new DateTime();
        $age = $today->diff($birthDate)->y;
        
        if ($age < 18) {
            echo json_encode(['error' => 'You must be at least 18 years old']);
            return;
        }
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM TBL_USERS WHERE email = ?");
        $stmt->execute([$data['email']]);
        
        if ($stmt->fetch()) {
            echo json_encode(['error' => 'Email already registered']);
            return;
        }
        
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM TBL_USERS WHERE username = ?");
        $stmt->execute([$data['username']]);
        
        if ($stmt->fetch()) {
            echo json_encode(['error' => 'Username already taken']);
            return;
        }
        
        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Insert new user
        $stmt = $pdo->prepare("
            INSERT INTO TBL_USERS (username, email, password, gender, dob, bio, profile_pic, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
        ");
        
        $stmt->execute([
            $data['username'],
            $data['email'],
            $hashedPassword,
            $data['gender'],
            $data['dob'],
            $data['bio'] ?? '',
            $profilePicPath
        ]);
        
        $userId = $pdo->lastInsertId();
        
        // Create user preferences
        $stmt = $pdo->prepare("
            INSERT INTO TBL_USER_PREFERENCES (user_id, min_age, max_age, preferred_gender, max_distance) 
            VALUES (?, 18, 100, 'all', 50)
        ");
        $stmt->execute([$userId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful! Welcome to LoveNet!',
            'user_id' => $userId
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['error' => 'Registration failed: ' . $e->getMessage()]);
    }
}

function handleLogin($pdo, $data) {
    try {
        if (empty($data['email']) || empty($data['password'])) {
            echo json_encode(['error' => 'Email and password are required']);
            return;
        }
        
        // Get user by email
        $stmt = $pdo->prepare("
            SELECT id, username, email, password, status 
            FROM TBL_USERS 
            WHERE email = ?
        ");
        $stmt->execute([$data['email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            echo json_encode(['error' => 'Invalid email or password']);
            return;
        }
        
        if ($user['status'] !== 'active') {
            echo json_encode(['error' => 'Account is banned or disabled']);
            return;
        }
        
        // Verify password
        if (!password_verify($data['password'], $user['password'])) {
            echo json_encode(['error' => 'Invalid email or password']);
            return;
        }
        
        // Start session and store user data
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful!',
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email']
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['error' => 'Login failed: ' . $e->getMessage()]);
    }
}

function getProfiles($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT id, username, gender, dob, bio, profile_pic, created_at,
                   TIMESTAMPDIFF(YEAR, dob, CURDATE()) as age
            FROM TBL_USERS 
            WHERE status = 'active'
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        $profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'profiles' => $profiles
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to get profiles: ' . $e->getMessage()]);
    }
}

function handleSearch($pdo, $data) {
    try {
        $minAge = $data['min_age'] ?? 18;
        $maxAge = $data['max_age'] ?? 100;
        $gender = $data['gender'] ?? '';
        $location = $data['location'] ?? '';
        
        $sql = "
            SELECT id, username, gender, dob, bio, profile_pic, created_at,
                   TIMESTAMPDIFF(YEAR, dob, CURDATE()) as age
            FROM TBL_USERS 
            WHERE status = 'active'
            AND TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN ? AND ?
        ";
        
        $params = [$minAge, $maxAge];
        
        if (!empty($gender)) {
            $sql .= " AND gender = ?";
            $params[] = $gender;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'profiles' => $profiles,
            'count' => count($profiles)
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['error' => 'Search failed: ' . $e->getMessage()]);
    }
}

function getUsers($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT id, username, email, gender, dob, bio, status, created_at,
                   TIMESTAMPDIFF(YEAR, dob, CURDATE()) as age
            FROM TBL_USERS 
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'users' => $users
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to get users: ' . $e->getMessage()]);
    }
}
?> 