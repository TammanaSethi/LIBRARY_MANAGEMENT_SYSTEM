<?php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Generate teacher registration code
if (isset($_POST['generate_code'])) {
    $code = strtoupper(bin2hex(random_bytes(4))); // 8 character code
    $admin_id = $_SESSION['user_id'];
    
    $sql = "INSERT INTO teacher_codes (code, created_by) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $code, $admin_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $success = "Teacher registration code generated: " . $code;
        logActivity($_SESSION['user_id'], 'Generated teacher code', "Generated code: $code");
    } else {
        $error = "Error generating code.";
    }
}

// Delete user
if (isset($_POST['delete_user']) && isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Delete related records first
        mysqli_query($conn, "DELETE FROM fines WHERE issue_id IN (SELECT id FROM book_issues WHERE student_id = $user_id)");
        mysqli_query($conn, "DELETE FROM book_issues WHERE student_id = $user_id");
        mysqli_query($conn, "DELETE FROM reservations WHERE student_id = $user_id");
        mysqli_query($conn, "DELETE FROM reviews WHERE user_id = $user_id");
        mysqli_query($conn, "DELETE FROM activity_log WHERE user_id = $user_id");
        
        // Finally delete the user
        mysqli_query($conn, "DELETE FROM users WHERE id = $user_id");
        
        mysqli_commit($conn);
        $success = "User deleted successfully.";
        logActivity($_SESSION['user_id'], 'Deleted user', "Deleted user ID: $user_id");
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error = "Error deleting user.";
    }
}

// Get all users except admin
$users = mysqli_query($conn, "
    SELECT id, username, email, role, status, created_at 
    FROM users 
    WHERE role != 'admin' 
    ORDER BY created_at DESC
");

// Get unused teacher codes
$teacher_codes = mysqli_query($conn, "
    SELECT tc.*, u.email as created_by_email 
    FROM teacher_codes tc 
    LEFT JOIN users u ON tc.created_by = u.id 
    WHERE tc.is_used = FALSE 
    ORDER BY tc.created_at DESC
");

// Get system statistics
$stats = getLibraryStatistics();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Library Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Library Management System - Admin</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Welcome, Administrator</h2>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Books</h5>
                        <h2><?php echo $stats['total_books']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Books Issued</h5>
                        <h2><?php echo $stats['books_issued']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Students</h5>
                        <h2><?php echo $stats['total_students']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5 class="card-title">Fines Collected</h5>
                        <h2>₹<?php echo number_format($stats['total_fines_collected'], 2); ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Teacher Code Generation -->
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Teacher Registration Codes</h4>
                <form method="POST" class="d-inline">
                    <button type="submit" name="generate_code" class="btn btn-primary">Generate New Code</button>
                </form>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Generated By</th>
                            <th>Created At</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($code = mysqli_fetch_assoc($teacher_codes)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($code['code']); ?></td>
                                <td><?php echo htmlspecialchars($code['created_by_email']); ?></td>
                                <td><?php echo formatDateTime($code['created_at']); ?></td>
                                <td><span class="badge bg-success">Available</span></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- User Management -->
        <div class="card mt-4">
            <div class="card-header">
                <h4>User Management</h4>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = mysqli_fetch_assoc($users)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $user['role'] === 'teacher' ? 'primary' : 'info'; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $user['status'] === 'approved' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo formatDateTime($user['created_at']); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="delete_user" class="btn btn-danger btn-sm">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 