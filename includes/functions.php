
<?php
// Database connection
function getDBConnection() {
    static $conn = null;
    if ($conn === null) {
        require_once _DIR_ . '/../config.php';
        $conn = $GLOBALS['conn'];
    }
    return $conn;
}

// Security functions
function clean($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Authentication functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /library/login.php');
        exit();
    }
}

function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: /library/unauthorized.php');
        exit();
    }
}

// Activity logging
function logActivity($user_id, $action) {
    global $conn;
    
    $query = "INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)";
    
    // Get client details
    $details = '';
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    try {
        $stmt = mysqli_prepare($conn, $query);
        if ($stmt === false) {
            throw new Exception('Failed to prepare statement: ' . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, "issss", $user_id, $action, $details, $ip, $user_agent);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return true;
    } catch (Exception $e) {
        // Log the error but don't stop execution
        error_log("Error logging activity: " . $e->getMessage());
        return false;
    }
}

// Add activity_logs table if it doesn't exist
function createActivityLogsTable() {
    global $conn;
    
    $sql = "CREATE TABLE IF NOT EXISTS activity_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        action VARCHAR(255) NOT NULL,
        details TEXT,
        ip_address VARCHAR(45),
        user_agent VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    
    if (!mysqli_query($conn, $sql)) {
        error_log("Error creating activity_logs table: " . mysqli_error($conn));
    }
}

// Call this when including functions.php
createActivityLogsTable();

// Email functions
function queueEmail($toEmail, $subject, $message) {
    $conn = getDBConnection();
    $sql = "INSERT INTO email_queue (to_email, subject, message) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $toEmail, $subject, $message);
    return mysqli_stmt_execute($stmt);
}

// Settings functions
function getSetting($key) {
    $conn = getDBConnection();
    $sql = "SELECT setting_value FROM settings WHERE setting_key = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $key);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row ? $row['setting_value'] : null;
}

function updateSetting($key, $value) {
    $conn = getDBConnection();
    $sql = "UPDATE settings SET setting_value = ? WHERE setting_key = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $value, $key);
    return mysqli_stmt_execute($stmt);
}

// Book functions
function getBookAvailability($bookId) {
    $conn = getDBConnection();
    $sql = "SELECT quantity, available_quantity FROM books WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $bookId);
    mysqli_stmt_execute($stmt);
    return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

function canIssueMoreBooks($userId) {
    $conn = getDBConnection();
    $maxBooks = (int)getSetting('max_books_per_student');
    
    $sql = "SELECT COUNT(*) as current_issues FROM book_issues WHERE student_id = ? AND status = 'issued'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    
    return $result['current_issues'] < $maxBooks;
}

// Fine calculation
function calculateFine($issueId) {
    $conn = getDBConnection();
    $fineRate = (int)getSetting('fine_rate');
    
    $sql = "SELECT DATEDIFF(CURRENT_DATE, due_date) as days_overdue 
            FROM book_issues 
            WHERE id = ? AND status = 'issued' AND CURRENT_DATE > due_date";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $issueId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    
    return $result ? max(0, $result['days_overdue'] * $fineRate) : 0;
}

// File upload handling
function uploadFile($file, $targetDir, $allowedTypes = ['jpg', 'jpeg', 'png']) {
    $fileName = basename($file['name']);
    $targetPath = $targetDir . '/' . $fileName;
    $fileType = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
    
    // Validate file type
    if (!in_array($fileType, $allowedTypes)) {
        return ['error' => 'Invalid file type'];
    }
    
    // Generate unique filename
    $fileName = uniqid() . '.' . $fileType;
    $targetPath = $targetDir . '/' . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => $fileName];
    }
    
    return ['error' => 'Failed to upload file'];
}

// Date and time utilities
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('F j, Y g:i A', strtotime($datetime));
}

// Pagination helper
function getPaginationLinks($totalItems, $itemsPerPage, $currentPage) {
    $totalPages = ceil($totalItems / $itemsPerPage);
    $links = [];
    
    for ($i = 1; $i <= $totalPages; $i++) {
        $links[] = [
            'page' => $i,
            'current' => $i === $currentPage,
            'url' => '?page=' . $i
        ];
    }
    
    return [
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages,
        'links' => $links
    ];
}

// Search functionality
function searchBooks($query, $category = null) {
    $conn = getDBConnection();
    $sql = "SELECT b.*, c.name as category_name 
            FROM books b 
            LEFT JOIN categories c ON b.category_id = c.id 
            WHERE (b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ?)";
    
    if ($category) {
        $sql .= " AND b.category_id = ?";
    }
    
    $stmt = mysqli_prepare($conn, $sql);
    $searchTerm = "%$query%";
    
    if ($category) {
        mysqli_stmt_bind_param($stmt, "sssi", $searchTerm, $searchTerm, $searchTerm, $category);
    } else {
        mysqli_stmt_bind_param($stmt, "sss", $searchTerm, $searchTerm, $searchTerm);
    }
    
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

// Statistics and reports
function getLibraryStatistics() {
    $conn = getDBConnection();
    $stats = [];
    
    // Total books
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM books");
    $stats['total_books'] = mysqli_fetch_assoc($result)['total'];
    
    // Books issued
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM book_issues WHERE status = 'issued'");
    $stats['books_issued'] = mysqli_fetch_assoc($result)['total'];
    
    // Total students
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'student'");
    $stats['total_students'] = mysqli_fetch_assoc($result)['total'];
    
    // Total fines collected
    $result = mysqli_query($conn, "SELECT SUM(amount) as total FROM fines WHERE paid = TRUE");
    $stats['total_fines_collected'] = mysqli_fetch_assoc($result)['total'] ?? 0;
    
    return $stats;
}

// Backup functionality
function createDatabaseBackup() {
    $conn = getDBConnection();
    $tables = [];
    $result = mysqli_query($conn, "SHOW TABLES");
    
    while ($row = mysqli_fetch_row($result)) {
        $tables[] = $row[0];
    }
    
    $backup = "";
    
    foreach ($tables as $table) {
        $result = mysqli_query($conn, "SELECT * FROM $table");
        $numFields = mysqli_num_fields($result);
        
        $backup .= "DROP TABLE IF EXISTS $table;";
        $row2 = mysqli_fetch_row(mysqli_query($conn, "SHOW CREATE TABLE $table"));
        $backup .= "\n\n" . $row2[1] . ";\n\n";
        
        while ($row = mysqli_fetch_row($result)) {
            $backup .= "INSERT INTO $table VALUES(";
            for ($j = 0; $j < $numFields; $j++) {
                $row[$j] = addslashes($row[$j]);
                if (isset($row[$j])) {
                    $backup .= '"' . $row[$j] . '"';
                } else {
                    $backup .= '""';
                }
                if ($j < ($numFields - 1)) {
                    $backup .= ',';
                }
            }
            $backup .= ");\n";
        }
        $backup .= "\n\n";
    }
    
    $backupFile = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    file_put_contents(_DIR_ . '/../backups/' . $backupFile, $backup);
    return $backupFile;
}
?>