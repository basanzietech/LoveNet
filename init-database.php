<?php
// LoveNet Database Initialization Script
// This script sets up the database with sample data

// Database configuration
$host = 'localhost';
$dbname = 'lovenet_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connected successfully!\n";
} catch(PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage() . "\n");
}

// Function to check if table exists
function tableExists($pdo, $tableName) {
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$tableName]);
    return $stmt->rowCount() > 0;
}

// Function to check if data exists
function dataExists($pdo, $tableName) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM $tableName");
    $stmt->execute();
    return $stmt->fetchColumn() > 0;
}

echo "\n🚀 Starting LoveNet Database Initialization...\n\n";

// Create tables if they don't exist
$tables = [
    'TBL_USERS' => "
        CREATE TABLE IF NOT EXISTS TBL_USERS (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            phone VARCHAR(20),
            password VARCHAR(255) NOT NULL,
            gender ENUM('male', 'female', 'other') NOT NULL,
            dob DATE NOT NULL,
            bio TEXT,
            profile_pic VARCHAR(255),
            location VARCHAR(100),
            status ENUM('active', 'banned') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_username (username),
            INDEX idx_status (status),
            INDEX idx_gender (gender),
            INDEX idx_created_at (created_at)
        )
    ",
    
    'TBL_ADMINS' => "
        CREATE TABLE IF NOT EXISTS TBL_ADMINS (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('super_admin', 'moderator') DEFAULT 'moderator',
            status ENUM('active', 'disabled') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_role (role),
            INDEX idx_status (status)
        )
    ",
    
    'TBL_MESSAGES' => "
        CREATE TABLE IF NOT EXISTS TBL_MESSAGES (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sender_id INT NOT NULL,
            receiver_id INT NOT NULL,
            message TEXT NOT NULL,
            is_read BOOLEAN DEFAULT FALSE,
            sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (sender_id) REFERENCES TBL_USERS(id) ON DELETE CASCADE,
            FOREIGN KEY (receiver_id) REFERENCES TBL_USERS(id) ON DELETE CASCADE,
            INDEX idx_sender_id (sender_id),
            INDEX idx_receiver_id (receiver_id),
            INDEX idx_sent_at (sent_at),
            INDEX idx_conversation (sender_id, receiver_id)
        )
    ",
    
    'TBL_REPORTS' => "
        CREATE TABLE IF NOT EXISTS TBL_REPORTS (
            id INT AUTO_INCREMENT PRIMARY KEY,
            reported_id INT NOT NULL,
            reporter_id INT NOT NULL,
            reason TEXT NOT NULL,
            status ENUM('pending', 'reviewed', 'resolved') DEFAULT 'pending',
            admin_notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (reported_id) REFERENCES TBL_USERS(id) ON DELETE CASCADE,
            FOREIGN KEY (reporter_id) REFERENCES TBL_USERS(id) ON DELETE CASCADE,
            INDEX idx_reported_id (reported_id),
            INDEX idx_reporter_id (reporter_id),
            INDEX idx_status (status),
            INDEX idx_created_at (created_at)
        )
    ",
    
    'TBL_VERIFICATIONS' => "
        CREATE TABLE IF NOT EXISTS TBL_VERIFICATIONS (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            selfie VARCHAR(255) NOT NULL,
            status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
            admin_notes TEXT,
            submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            reviewed_at TIMESTAMP NULL,
            reviewed_by INT NULL,
            FOREIGN KEY (user_id) REFERENCES TBL_USERS(id) ON DELETE CASCADE,
            FOREIGN KEY (reviewed_by) REFERENCES TBL_ADMINS(id) ON DELETE SET NULL,
            INDEX idx_user_id (user_id),
            INDEX idx_status (status),
            INDEX idx_submitted_at (submitted_at)
        )
    ",
    
    'TBL_LIKES' => "
        CREATE TABLE IF NOT EXISTS TBL_LIKES (
            id INT AUTO_INCREMENT PRIMARY KEY,
            liker_id INT NOT NULL,
            liked_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (liker_id) REFERENCES TBL_USERS(id) ON DELETE CASCADE,
            FOREIGN KEY (liked_id) REFERENCES TBL_USERS(id) ON DELETE CASCADE,
            UNIQUE KEY unique_like (liker_id, liked_id),
            INDEX idx_liker_id (liker_id),
            INDEX idx_liked_id (liked_id),
            INDEX idx_created_at (created_at)
        )
    ",
    
    'TBL_USER_PREFERENCES' => "
        CREATE TABLE IF NOT EXISTS TBL_USER_PREFERENCES (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            min_age INT DEFAULT 18,
            max_age INT DEFAULT 100,
            preferred_gender ENUM('male', 'female', 'other', 'all') DEFAULT 'all',
            max_distance INT DEFAULT 50,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES TBL_USERS(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_preferences (user_id)
        )
    "
];

// Create tables
foreach ($tables as $tableName => $sql) {
    if (!tableExists($pdo, $tableName)) {
        $pdo->exec($sql);
        echo "✅ Created table: $tableName\n";
    } else {
        echo "ℹ️  Table already exists: $tableName\n";
    }
}

echo "\n📊 Inserting sample data...\n\n";

// Insert sample admins
if (!dataExists($pdo, 'TBL_ADMINS')) {
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $superAdminPassword = password_hash('superadmin123', PASSWORD_DEFAULT);
    
    $admins = [
        ['superadmin', 'admin@lovenet.com', $superAdminPassword, 'super_admin'],
        ['moderator1', 'moderator1@lovenet.com', $adminPassword, 'moderator'],
        ['moderator2', 'moderator2@lovenet.com', $adminPassword, 'moderator']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO TBL_ADMINS (username, email, password, role) VALUES (?, ?, ?, ?)");
    foreach ($admins as $admin) {
        $stmt->execute($admin);
    }
    echo "✅ Inserted " . count($admins) . " admin accounts\n";
    echo "   👤 Super Admin: admin@lovenet.com / superadmin123\n";
    echo "   👤 Moderator: moderator1@lovenet.com / admin123\n";
} else {
    echo "ℹ️  Admin data already exists\n";
}

// Insert sample users
if (!dataExists($pdo, 'TBL_USERS')) {
    $userPassword = password_hash('password123', PASSWORD_DEFAULT);
    
    $users = [
        ['Sarah Johnson', 'sarah.j@email.com', 'female', '1996-05-15', 'Adventure seeker and coffee enthusiast. Looking for someone to explore the world with!', 'New York, NY', 'https://images.unsplash.com/photo-1494790108755-2616b612b786?w=300&h=400&fit=crop&crop=face'],
        ['Michael Chen', 'michael.c@email.com', 'male', '1992-03-22', 'Music producer and dog lover. Passionate about creating meaningful connections.', 'Los Angeles, CA', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=400&fit=crop&crop=face'],
        ['Emma Wilson', 'emma.w@email.com', 'female', '1998-08-10', 'Art teacher and yoga instructor. Love spending time outdoors and trying new restaurants.', 'Chicago, IL', 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=300&h=400&fit=crop&crop=face'],
        ['David Brown', 'david.b@email.com', 'male', '1994-12-05', 'Software engineer who loves surfing and cooking. Looking for someone to share life\'s adventures.', 'Miami, FL', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=300&h=400&fit=crop&crop=face'],
        ['Jessica Lee', 'jessica.l@email.com', 'female', '1997-01-20', 'Bookworm and nature lover. Enjoy hiking, photography, and deep conversations.', 'Seattle, WA', 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=300&h=400&fit=crop&crop=face'],
        ['Alex Smith', 'alex.s@email.com', 'male', '1995-07-14', 'Entrepreneur and fitness enthusiast. Love traveling and meeting new people.', 'Austin, TX', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=300&h=400&fit=crop&crop=face'],
        ['Maria Garcia', 'maria.g@email.com', 'female', '1993-11-08', 'Chef and food blogger. Love cooking, traveling, and trying new cuisines.', 'San Francisco, CA', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=300&h=400&fit=crop&crop=face'],
        ['James Wilson', 'james.w@email.com', 'male', '1991-04-12', 'Doctor and marathon runner. Looking for someone to share active lifestyle.', 'Boston, MA', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=400&fit=crop&crop=face'],
        ['Lisa Anderson', 'lisa.a@email.com', 'female', '1999-02-28', 'Graphic designer and artist. Love museums, galleries, and creative projects.', 'Portland, OR', 'https://images.unsplash.com/photo-1494790108755-2616b612b786?w=300&h=400&fit=crop&crop=face'],
        ['Robert Taylor', 'robert.t@email.com', 'male', '1990-09-15', 'Architect and photographer. Passionate about design and capturing beautiful moments.', 'Denver, CO', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=300&h=400&fit=crop&crop=face']
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO TBL_USERS (username, email, password, gender, dob, bio, location, profile_pic, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
    ");
    
    foreach ($users as $user) {
        $stmt->execute([$user[0], $user[1], $userPassword, $user[2], $user[3], $user[4], $user[5], $user[6]]);
    }
    echo "✅ Inserted " . count($users) . " user accounts\n";
    echo "   👤 Test User: sarah.j@email.com / password123\n";
} else {
    echo "ℹ️  User data already exists\n";
}

// Insert sample messages
if (!dataExists($pdo, 'TBL_MESSAGES')) {
    $messages = [
        [1, 2, 'Hi! I really enjoyed your profile. Would you like to grab coffee sometime?'],
        [2, 1, 'That sounds great! How about tomorrow at 3 PM?'],
        [3, 4, 'Thanks for the message! I\'d love to meet up.'],
        [4, 3, 'Great! How about this weekend?'],
        [5, 6, 'Hi Alex! I saw we have similar interests. Would you like to chat?'],
        [6, 5, 'Absolutely! I\'d love to get to know you better.'],
        [7, 8, 'Hi James! I love your profile. Are you free this weekend?'],
        [8, 7, 'Hi Maria! Yes, I\'d love to meet up. How about Saturday?'],
        [9, 10, 'Hi Robert! Your photography is amazing. Would you like to grab coffee?'],
        [10, 9, 'Thank you! I\'d love to meet up and chat about art and design.']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO TBL_MESSAGES (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    foreach ($messages as $message) {
        $stmt->execute($message);
    }
    echo "✅ Inserted " . count($messages) . " messages\n";
} else {
    echo "ℹ️  Message data already exists\n";
}

// Insert sample reports
if (!dataExists($pdo, 'TBL_REPORTS')) {
    $reports = [
        [4, 1, 'Inappropriate messages'],
        [6, 3, 'Fake profile'],
        [2, 5, 'Harassment'],
        [8, 7, 'Spam messages'],
        [10, 9, 'Inappropriate behavior']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO TBL_REPORTS (reported_id, reporter_id, reason) VALUES (?, ?, ?)");
    foreach ($reports as $report) {
        $stmt->execute($report);
    }
    echo "✅ Inserted " . count($reports) . " reports\n";
} else {
    echo "ℹ️  Report data already exists\n";
}

// Insert sample verifications
if (!dataExists($pdo, 'TBL_VERIFICATIONS')) {
    $verifications = [
        [1, 'https://images.unsplash.com/photo-1494790108755-2616b612b786?w=100&h=100&fit=crop&crop=face', 'verified'],
        [2, 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&crop=face', 'pending'],
        [3, 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100&h=100&fit=crop&crop=face', 'rejected'],
        [4, 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop&crop=face', 'verified'],
        [5, 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=100&h=100&fit=crop&crop=face', 'pending']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO TBL_VERIFICATIONS (user_id, selfie, status) VALUES (?, ?, ?)");
    foreach ($verifications as $verification) {
        $stmt->execute($verification);
    }
    echo "✅ Inserted " . count($verifications) . " verifications\n";
} else {
    echo "ℹ️  Verification data already exists\n";
}

// Insert sample likes
if (!dataExists($pdo, 'TBL_LIKES')) {
    $likes = [
        [1, 2], [2, 1], // Sarah & Michael - mutual match
        [3, 4], [4, 3], // Emma & David - mutual match
        [5, 6], [6, 5], // Jessica & Alex - mutual match
        [7, 8], [8, 7], // Maria & James - mutual match
        [9, 10], [10, 9], // Lisa & Robert - mutual match
        [1, 3], [1, 5], [1, 7], // Sarah likes multiple people
        [2, 4], [2, 6], [2, 8], // Michael likes multiple people
        [3, 1], [3, 5], [3, 7], // Emma likes multiple people
        [4, 2], [4, 6], [4, 8], // David likes multiple people
        [5, 1], [5, 3], [5, 7], // Jessica likes multiple people
        [6, 2], [6, 4], [6, 8]  // Alex likes multiple people
    ];
    
    $stmt = $pdo->prepare("INSERT INTO TBL_LIKES (liker_id, liked_id) VALUES (?, ?)");
    foreach ($likes as $like) {
        try {
            $stmt->execute($like);
        } catch (PDOException $e) {
            // Ignore duplicate key errors
        }
    }
    echo "✅ Inserted likes data\n";
} else {
    echo "ℹ️  Likes data already exists\n";
}

// Insert sample user preferences
if (!dataExists($pdo, 'TBL_USER_PREFERENCES')) {
    $preferences = [
        [1, 25, 35, 'male', 30],
        [2, 23, 32, 'female', 25],
        [3, 26, 38, 'male', 40],
        [4, 24, 34, 'female', 35],
        [5, 25, 36, 'male', 30],
        [6, 23, 33, 'female', 25],
        [7, 26, 40, 'male', 35],
        [8, 24, 36, 'female', 30],
        [9, 25, 38, 'male', 40],
        [10, 23, 35, 'female', 35]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO TBL_USER_PREFERENCES (user_id, min_age, max_age, preferred_gender, max_distance) VALUES (?, ?, ?, ?, ?)");
    foreach ($preferences as $pref) {
        $stmt->execute($pref);
    }
    echo "✅ Inserted user preferences\n";
} else {
    echo "ℹ️  User preferences already exist\n";
}

// Create views
echo "\n📋 Creating database views...\n";

$views = [
    'v_user_stats' => "
        CREATE OR REPLACE VIEW v_user_stats AS
        SELECT 
            COUNT(*) as total_users,
            COUNT(CASE WHEN status = 'active' THEN 1 END) as active_users,
            COUNT(CASE WHEN status = 'banned' THEN 1 END) as banned_users,
            COUNT(CASE WHEN gender = 'male' THEN 1 END) as male_users,
            COUNT(CASE WHEN gender = 'female' THEN 1 END) as female_users,
            COUNT(CASE WHEN gender = 'other' THEN 1 END) as other_users
        FROM TBL_USERS
    ",
    
    'v_matches' => "
        CREATE OR REPLACE VIEW v_matches AS
        SELECT 
            l1.liker_id,
            l1.liked_id,
            u1.username as liker_name,
            u2.username as liked_name,
            l1.created_at as match_date
        FROM TBL_LIKES l1
        INNER JOIN TBL_LIKES l2 ON l1.liker_id = l2.liked_id AND l1.liked_id = l2.liker_id
        INNER JOIN TBL_USERS u1 ON l1.liker_id = u1.id
        INNER JOIN TBL_USERS u2 ON l1.liked_id = u2.id
        WHERE l1.liker_id < l1.liked_id
    "
];

foreach ($views as $viewName => $sql) {
    try {
        $pdo->exec($sql);
        echo "✅ Created view: $viewName\n";
    } catch (Exception $e) {
        echo "ℹ️  View already exists: $viewName\n";
    }
}

echo "\n🎉 Database initialization completed successfully!\n\n";

echo "📊 Summary:\n";
echo "   👥 Users: " . $pdo->query("SELECT COUNT(*) FROM TBL_USERS")->fetchColumn() . "\n";
echo "   👤 Admins: " . $pdo->query("SELECT COUNT(*) FROM TBL_ADMINS")->fetchColumn() . "\n";
echo "   💬 Messages: " . $pdo->query("SELECT COUNT(*) FROM TBL_MESSAGES")->fetchColumn() . "\n";
echo "   🚨 Reports: " . $pdo->query("SELECT COUNT(*) FROM TBL_REPORTS")->fetchColumn() . "\n";
echo "   ✅ Verifications: " . $pdo->query("SELECT COUNT(*) FROM TBL_VERIFICATIONS")->fetchColumn() . "\n";
echo "   ❤️  Likes: " . $pdo->query("SELECT COUNT(*) FROM TBL_LIKES")->fetchColumn() . "\n\n";

echo "🔑 Login Credentials:\n";
echo "   👤 Super Admin: admin@lovenet.com / superadmin123\n";
echo "   👤 Moderator: moderator1@lovenet.com / admin123\n";
echo "   👤 Test User: sarah.j@email.com / password123\n\n";

echo "🌐 Access URLs:\n";
echo "   🏠 Main Site: http://localhost:8000\n";
echo "   ⚙️  Admin Panel: http://localhost:8000/admin-dashboard.html\n\n";

echo "✨ LoveNet is ready to use!\n";
?> 