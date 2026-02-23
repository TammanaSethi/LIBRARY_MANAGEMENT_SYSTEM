<?php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

// Check if student is approved
$student_id = $_SESSION['user_id'];
$check_status = mysqli_query($conn, "SELECT status FROM users WHERE id = $student_id");
$status = mysqli_fetch_assoc($check_status)['status'];

if ($status != 'approved') {
    header("Location: ../login.php");
    exit();
}

// Calculate and update fines for overdue books
$update_fines_query = "
    INSERT INTO fines (issue_id, amount)
    SELECT 
        bi.id,
        GREATEST(DATEDIFF(CURRENT_DATE, DATE_ADD(bi.issue_date, INTERVAL 7 DAY)) * 50, 0) as fine_amount
    FROM book_issues bi
    LEFT JOIN fines f ON bi.id = f.issue_id
    WHERE bi.student_id = ? 
    AND bi.status = 'issued'
    AND f.id IS NULL
    AND DATEDIFF(CURRENT_DATE, bi.issue_date) > 7
";

$stmt = mysqli_prepare($conn, $update_fines_query);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);

// Handle Book Issue
if (isset($_POST['issue_book'])) {
    $book_id = mysqli_real_escape_string($conn, $_POST['book_id']);
    $return_date = mysqli_real_escape_string($conn, $_POST['return_date']);
    
    // Check if book is available
    $check_query = "SELECT quantity FROM books WHERE id = ? AND quantity > 0";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "i", $book_id);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($result) > 0) {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Insert into book_issues
            $issue_query = "INSERT INTO book_issues (book_id, student_id, issue_date, return_date) 
                           VALUES (?, ?, CURRENT_TIMESTAMP, ?)";
            $issue_stmt = mysqli_prepare($conn, $issue_query);
            mysqli_stmt_bind_param($issue_stmt, "iis", $book_id, $student_id, $return_date);
            mysqli_stmt_execute($issue_stmt);
            
            // Update book quantity
            $update_query = "UPDATE books SET quantity = quantity - 1 WHERE id = ?";
            $update_stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($update_stmt, "i", $book_id);
            mysqli_stmt_execute($update_stmt);
            
            mysqli_commit($conn);
            $success = "Book issued successfully!";
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = "Error issuing book. Please try again.";
        }
    } else {
        $error = "Book is not available for issue.";
    }
}

// Handle Reissue and Return
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['reissue'])) {
        $issue_id = mysqli_real_escape_string($conn, $_POST['issue_id']);
        
        // Check if book is eligible for reissue
        $check_query = "SELECT * FROM book_issues WHERE id = ? AND reissued = 0 AND status = 'issued'";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, "i", $issue_id);
        mysqli_stmt_execute($check_stmt);
        $result = mysqli_stmt_get_result($check_stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            // Calculate new return date (7 days from current return date)
            $current_return_date = $row['return_date'];
            $new_return_date = date('Y-m-d', strtotime($current_return_date . ' + 7 days'));
            
            // Update the return date and mark as reissued
            $update_query = "UPDATE book_issues 
                            SET return_date = ?, reissued = 1 
                            WHERE id = ?";
            $update_stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($update_stmt, "si", $new_return_date, $issue_id);
            
            if (mysqli_stmt_execute($update_stmt)) {
                $success = "Book reissued successfully! New return date: " . $new_return_date;
            } else {
                $error = "Error reissuing book.";
            }
        } else {
            $error = "Book is not eligible for reissue.";
        }
    }

    if (isset($_POST['return'])) {
        $issue_id = mysqli_real_escape_string($conn, $_POST['issue_id']);
        
        $update_query = "UPDATE book_issues SET 
                        status = 'returned',
                        actual_return_date = CURRENT_TIMESTAMP 
                        WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "i", $issue_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Book returned successfully!";
        } else {
            $error = "Error returning book.";
        }
    }
}

// Get student's issued books
$student_id = $_SESSION['user_id'];
$query = "SELECT bi.*, b.title, b.author,
          IFNULL((SELECT amount FROM fines WHERE issue_id = bi.id), 0) as fine_amount,
          (SELECT paid FROM fines WHERE issue_id = bi.id) as paid
          FROM book_issues bi 
          JOIN books b ON bi.book_id = b.id 
          WHERE bi.student_id = ? AND bi.status = 'issued'
          ORDER BY bi.issue_date DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Get available books
$available_books = mysqli_query($conn, "SELECT * FROM books WHERE quantity > 0");

// Calculate total unpaid fines
$total_fines = mysqli_query($conn, "
    SELECT SUM(amount) as total_fine
    FROM fines f
    JOIN book_issues bi ON f.issue_id = bi.id
    WHERE bi.student_id = $student_id
    AND f.paid = FALSE
");
$total_fine_amount = mysqli_fetch_assoc($total_fines)['total_fine'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Library Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Student Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../contact.php">Contact Us</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
        
        <?php if ($total_fine_amount > 0): ?>
            <div class="alert alert-warning">
                You have pending fines of Rs. <?php echo number_format($total_fine_amount, 2); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Available Books -->
        <div class="card mt-4">
            <div class="card-header">
                <h4>Available Books</h4>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>ISBN</th>
                            <th>Available Quantity</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($book = mysqli_fetch_assoc($available_books)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($book['title']); ?></td>
                                <td><?php echo htmlspecialchars($book['author']); ?></td>
                                <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                                <td><?php echo htmlspecialchars($book['quantity']); ?></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                        <input type="date" name="return_date" required 
                                               min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                               max="<?php echo date('Y-m-d', strtotime('+7 days')); ?>">
                                        <button type="submit" name="issue_book" class="btn btn-primary btn-sm">Issue Book</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- My Issued Books -->
        <div class="card mt-4 mb-4">
            <div class="card-header">
                <h4>My Issued Books</h4>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Fine (Rs.)</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($book = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($book['title']); ?></td>
                                <td><?php echo htmlspecialchars($book['author']); ?></td>
                                <td><?php echo htmlspecialchars($book['issue_date']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($book['return_date']); ?>
                                </td>
                                <td>
                                    <?php if ($book['fine_amount'] > 0): ?>
                                        <span class="text-danger">
                                            <?php echo number_format($book['fine_amount'], 2); ?>
                                            <?php echo $book['paid'] ? ' (Paid)' : ' (Unpaid)'; ?>
                                        </span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <?php if (!$book['reissued'] && strtotime($book['return_date']) > time()): ?>
                                            <form method="POST" class="me-2">
                                                <input type="hidden" name="issue_id" value="<?php echo $book['id']; ?>">
                                                <button type="submit" name="reissue" class="btn btn-warning btn-sm">Reissue</button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST">
                                            <input type="hidden" name="issue_id" value="<?php echo $book['id']; ?>">
                                            <button type="submit" name="return" class="btn btn-success btn-sm">Return</button>
                                        </form>
                                    </div>
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