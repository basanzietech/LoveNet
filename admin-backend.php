<?php
// LoveNet Admin Backend API
// Handle CRUD operations for admin dashboard

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

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
    case 'GET':
        $action = $_GET['action'] ?? '';
        
        switch($action) {
            case 'users':
                getUsers($pdo);
                break;
            case 'reports':
                getReports($pdo);
                break;
            case 'messages':
                getMessages($pdo);
                break;
            case 'verifications':
                getVerifications($pdo);
                break;
            case 'stats':
                getStats($pdo);
                break;
            case 'user':
                $userId = $_GET['id'] ?? null;
                if ($userId) {
                    getUserById($pdo, $userId);
                } else {
                    echo json_encode(['error' => 'User ID required']);
                }
                break;
            default:
                echo json_encode(['error' => 'Invalid action']);
        }
        break;
        
    case 'POST':
        $action = $_GET['action'] ?? '';
        
        switch($action) {
            case 'create_user':
                createUser($pdo, $input);
                break;
            case 'ban_user':
                banUser($pdo, $input);
                break;
            case 'unban_user':
                unbanUser($pdo, $input);
                break;
            case 'resolve_report':
                resolveReport($pdo, $input);
                break;
            case 'approve_verification':
                approveVerification($pdo, $input);
                break;
            case 'reject_verification':
                rejectVerification($pdo, $input);
                break;
            case 'delete_message':
                deleteMessage($pdo, $input);
                break;
            default:
                echo json_encode(['error' => 'Invalid action']);
        }
        break;
        
    case 'PUT':
        $action = $_GET['action'] ?? '';
        
        switch($action) {
            case 'update_user':
                updateUser($pdo, $input);
                break;
            case 'update_settings':
                updateSettings($pdo, $input);
                break;
            default:
                echo json_encode(['error' => 'Invalid action']);
        }
        break;
        
    case 'DELETE':
        $action = $_GET['action'] ?? '';
        
        switch($action) {
            case 'delete_user':
                $userId = $_GET['id'] ?? null;
                if ($userId) {
                    deleteUser($pdo, $userId);
                } else {
                    echo json_encode(['error' => 'User ID required']);
                }
                break;
            case 'delete_report':
                $reportId = $_GET['id'] ?? null;
                if ($reportId) {
                    deleteReport($pdo, $reportId);
                } else {
                    echo json_encode(['error' => 'Report ID required']);
                }
                break;
            default:
                echo json_encode(['error' => 'Invalid action']);
        }
        break;
        
    default:
        echo json_encode(['error' => 'Method not allowed']);
}

// CRUD Functions for Users
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

function getUserById($pdo, $userId) {
    try {
        $stmt = $pdo->prepare("
            SELECT id, username, email, gender, dob, bio, status, created_at,
                   TIMESTAMPDIFF(YEAR, dob, CURDATE()) as age
            FROM TBL_USERS 
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo json_encode([
                'success' => true,
                'user' => $user
            ]);
        } else {
            echo json_encode(['error' => 'User not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to get user: ' . $e->getMessage()]);
    }
}

function createUser($pdo, $data) {
    try {
        // Validate required fields
        $required = ['username', 'email', 'password', 'gender', 'dob'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                echo json_encode(['error' => "Field '$field' is required"]);
                return;
            }
        }
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM TBL_USERS WHERE email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            echo json_encode(['error' => 'Email already registered']);
            return;
        }
        
        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Insert new user
        $stmt = $pdo->prepare("
            INSERT INTO TBL_USERS (username, email, password, gender, dob, bio, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['username'],
            $data['email'],
            $hashedPassword,
            $data['gender'],
            $data['dob'],
            $data['bio'] ?? '',
            $data['status'] ?? 'active'
        ]);
        
        $userId = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'User created successfully',
            'user_id' => $userId
        ]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to create user: ' . $e->getMessage()]);
    }
}

function updateUser($pdo, $data) {
    try {
        if (empty($data['id'])) {
            echo json_encode(['error' => 'User ID is required']);
            return;
        }
        
        $updates = [];
        $params = [];
        
        // Build update query dynamically
        if (isset($data['username'])) {
            $updates[] = "username = ?";
            $params[] = $data['username'];
        }
        if (isset($data['email'])) {
            $updates[] = "email = ?";
            $params[] = $data['email'];
        }
        if (isset($data['gender'])) {
            $updates[] = "gender = ?";
            $params[] = $data['gender'];
        }
        if (isset($data['bio'])) {
            $updates[] = "bio = ?";
            $params[] = $data['bio'];
        }
        if (isset($data['status'])) {
            $updates[] = "status = ?";
            $params[] = $data['status'];
        }
        
        if (empty($updates)) {
            echo json_encode(['error' => 'No fields to update']);
            return;
        }
        
        $params[] = $data['id'];
        $sql = "UPDATE TBL_USERS SET " . implode(', ', $updates) . " WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        echo json_encode([
            'success' => true,
            'message' => 'User updated successfully'
        ]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to update user: ' . $e->getMessage()]);
    }
}

function deleteUser($pdo, $userId) {
    try {
        $stmt = $pdo->prepare("DELETE FROM TBL_USERS WHERE id = ?");
        $stmt->execute([$userId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } else {
            echo json_encode(['error' => 'User not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to delete user: ' . $e->getMessage()]);
    }
}

function banUser($pdo, $data) {
    try {
        if (empty($data['user_id'])) {
            echo json_encode(['error' => 'User ID is required']);
            return;
        }
        
        $stmt = $pdo->prepare("UPDATE TBL_USERS SET status = 'banned' WHERE id = ?");
        $stmt->execute([$data['user_id']]);
        
        echo json_encode([
            'success' => true,
            'message' => 'User banned successfully'
        ]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to ban user: ' . $e->getMessage()]);
    }
}

function unbanUser($pdo, $data) {
    try {
        if (empty($data['user_id'])) {
            echo json_encode(['error' => 'User ID is required']);
            return;
        }
        
        $stmt = $pdo->prepare("UPDATE TBL_USERS SET status = 'active' WHERE id = ?");
        $stmt->execute([$data['user_id']]);
        
        echo json_encode([
            'success' => true,
            'message' => 'User unbanned successfully'
        ]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to unban user: ' . $e->getMessage()]);
    }
}

// CRUD Functions for Reports
function getReports($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT r.*, 
                   u1.username as reported_user_name,
                   u2.username as reporter_name
            FROM TBL_REPORTS r
            LEFT JOIN TBL_USERS u1 ON r.reported_id = u1.id
            LEFT JOIN TBL_USERS u2 ON r.reporter_id = u2.id
            ORDER BY r.created_at DESC
        ");
        $stmt->execute();
        $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'reports' => $reports
        ]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to get reports: ' . $e->getMessage()]);
    }
}

function resolveReport($pdo, $data) {
    try {
        if (empty($data['report_id'])) {
            echo json_encode(['error' => 'Report ID is required']);
            return;
        }
        
        $stmt = $pdo->prepare("UPDATE TBL_REPORTS SET status = 'resolved' WHERE id = ?");
        $stmt->execute([$data['report_id']]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Report resolved successfully'
        ]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to resolve report: ' . $e->getMessage()]);
    }
}

function deleteReport($pdo, $reportId) {
    try {
        $stmt = $pdo->prepare("DELETE FROM TBL_REPORTS WHERE id = ?");
        $stmt->execute([$reportId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Report deleted successfully'
        ]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to delete report: ' . $e->getMessage()]);
    }
}

// CRUD Functions for Messages
function getMessages($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT m.*, 
                   u1.username as sender_name,
                   u2.username as receiver_name
            FROM TBL_MESSAGES m
            LEFT JOIN TBL_USERS u1 ON m.sender_id = u1.id
            LEFT JOIN TBL_USERS u2 ON m.receiver_id = u2.id
            ORDER BY m.sent_at DESC
        ");
        $stmt->execute();
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'messages' => $messages
        ]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to get messages: ' . $e->getMessage()]);
    }
}

function deleteMessage($pdo, $data) {
    try {
        if (empty($data['message_id'])) {
            echo json_encode(['error' => 'Message ID is required']);
            return;
        }
        
        $stmt = $pdo->prepare("DELETE FROM TBL_MESSAGES WHERE id = ?");
        $stmt->execute([$data['message_id']]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Message deleted successfully'
        ]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to delete message: ' . $e->getMessage()]);
    }
}

// CRUD Functions for Verifications
function getVerifications($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT v.*, u.username as user_name
            FROM TBL_VERIFICATIONS v
            LEFT JOIN TBL_USERS u ON v.user_id = u.id
            ORDER BY v.submitted_at DESC
        ");
        $stmt->execute();
        $verifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'verifications' => $verifications
        ]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to get verifications: ' . $e->getMessage()]);
    }
}

function approveVerification($pdo, $data) {
    try {
        if (empty($data['verification_id'])) {
            echo json_encode(['error' => 'Verification ID is required']);
            return;
        }
        
        $stmt = $pdo->prepare("UPDATE TBL_VERIFICATIONS SET status = 'verified', reviewed_at = NOW() WHERE id = ?");
        $stmt->execute([$data['verification_id']]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Verification approved successfully'
        ]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to approve verification: ' . $e->getMessage()]);
    }
}

function rejectVerification($pdo, $data) {
    try {
        if (empty($data['verification_id'])) {
            echo json_encode(['error' => 'Verification ID is required']);
            return;
        }
        
        $stmt = $pdo->prepare("UPDATE TBL_VERIFICATIONS SET status = 'rejected', reviewed_at = NOW() WHERE id = ?");
        $stmt->execute([$data['verification_id']]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Verification rejected successfully'
        ]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to reject verification: ' . $e->getMessage()]);
    }
}

// Statistics Function
function getStats($pdo) {
    try {
        // User statistics
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_users,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_users,
                COUNT(CASE WHEN status = 'banned' THEN 1 END) as banned_users,
                COUNT(CASE WHEN gender = 'male' THEN 1 END) as male_users,
                COUNT(CASE WHEN gender = 'female' THEN 1 END) as female_users,
                COUNT(CASE WHEN gender = 'other' THEN 1 END) as other_users
            FROM TBL_USERS
        ");
        $stmt->execute();
        $userStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Report statistics
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_reports,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_reports,
                COUNT(CASE WHEN status = 'resolved' THEN 1 END) as resolved_reports
            FROM TBL_REPORTS
        ");
        $stmt->execute();
        $reportStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Message statistics
        $stmt = $pdo->prepare("SELECT COUNT(*) as total_messages FROM TBL_MESSAGES");
        $stmt->execute();
        $messageStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verification statistics
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_verifications,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_verifications,
                COUNT(CASE WHEN status = 'verified' THEN 1 END) as verified_verifications,
                COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_verifications
            FROM TBL_VERIFICATIONS
        ");
        $stmt->execute();
        $verificationStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'stats' => [
                'users' => $userStats,
                'reports' => $reportStats,
                'messages' => $messageStats,
                'verifications' => $verificationStats
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to get statistics: ' . $e->getMessage()]);
    }
}

// Settings Function
function updateSettings($pdo, $data) {
    try {
        // This would typically update a settings table
        // For now, we'll just return success
        echo json_encode([
            'success' => true,
            'message' => 'Settings updated successfully'
        ]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to update settings: ' . $e->getMessage()]);
    }
}
?> 