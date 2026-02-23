<?php
session_start();
require_once '../config.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../login.php");
    exit();
}

// Handle student approval/rejection
if (isset($_POST['action']) && isset($_POST['student_id'])) {
    $action = $_POST['action'];
    $student_id = $_POST['student_id'];
    $status = ($action == 'approve') ? 'approved' : 'rejected';
    
    $sql = "UPDATE users SET status = ? WHERE id = ? AND role = 'student'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $status, $student_id);
    mysqli_stmt_execute($stmt);
}

// Handle fine payment marking
if (isset($_POST['mark_paid']) && isset($_POST['fine_id'])) {
    $fine_id = (int)$_POST['fine_id'];
    mysqli_query($conn, "UPDATE fines SET paid = TRUE WHERE id = $fine_id");
}

// Handle book addition
if (isset($_POST['add_book'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $author = mysqli_real_escape_string($conn, $_POST['author']);
    $isbn = mysqli_real_escape_string($conn, $_POST['isbn']);
    $quantity = (int)$_POST['quantity'];
    
    $sql = "INSERT INTO books (title, author, isbn, quantity, added_by) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssii", $title, $author, $isbn, $quantity, $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
}

// Get pending students
$pending_students = mysqli_query($conn, "SELECT * FROM users WHERE role = 'student' AND status = 'pending'");

// Get all books
$books = mysqli_query($conn, "SELECT * FROM books");

// Get all issued books with fine information
$issued_books = mysqli_query($conn, "
    SELECT 
        bi.*, 
        b.title, 
        u.username,
        GREATEST(DATEDIFF(CURRENT_DATE, DATE_ADD(bi.issue_date, INTERVAL 7 DAY)) * 50, 0) as fine_amount,
        f.id as fine_id,
        f.paid
    FROM book_issues bi 
    JOIN books b ON bi.book_id = b.id 
    JOIN users u ON bi.student_id = u.id 
    LEFT JOIN fines f ON bi.id = f.issue_id
    WHERE bi.status = 'issued'
    ORDER BY bi.issue_date DESC
");

// Get total unpaid fines
$total_fines = mysqli_query($conn, "
    SELECT SUM(amount) as total_fine
    FROM fines
    WHERE paid = FALSE
");
$total_fine_amount = mysqli_fetch_assoc($total_fines)['total_fine'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - Library Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navigation Bar for Teacher Interface -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Teacher Dashboard</a>
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
            <div class="alert alert-info">
                Total unpaid fines: Rs. <?php echo number_format($total_fine_amount, 2); ?>
            </div>
        <?php endif; ?>
        
        <!-- Pending Student Approvals -->
        <div class="card mt-4">
            <div class="card-header">
                <h4>Pending Student Approvals</h4>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($student = mysqli_fetch_assoc($pending_students)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['username']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                        <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                                        <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add New Book -->
        <div class="card mt-4">
            <div class="card-header">
                <h4>Add New Book</h4>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-3">
                            <input type="text" name="title" class="form-control" placeholder="Book Title" required>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="author" class="form-control" placeholder="Author" required>
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="isbn" class="form-control" placeholder="ISBN" required>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="quantity" class="form-control" placeholder="Quantity" required min="1">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="add_book" class="btn btn-primary">Add Book</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Book List -->
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
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($book = mysqli_fetch_assoc($books)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($book['title']); ?></td>
                                <td><?php echo htmlspecialchars($book['author']); ?></td>
                                <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                                <td><?php echo htmlspecialchars($book['quantity']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Issued Books and Fines -->
        <div class="card mt-4 mb-4">
            <div class="card-header">
                <h4>Currently Issued Books and Fines</h4>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Student Name</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Days Overdue</th>
                            <th>Fine (Rs.)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($issue = mysqli_fetch_assoc($issued_books)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($issue['title']); ?></td>
                                <td><?php echo htmlspecialchars($issue['username']); ?></td>
                                <td><?php echo htmlspecialchars($issue['issue_date']); ?></td>
                                <td>
                                    <?php 
                                        $due_date = date('Y-m-d', strtotime($issue['issue_date'] . ' +7 days'));
                                        echo htmlspecialchars($due_date);
                                    ?>
                                </td>
                                <td>
                                    <?php
                                        $days_overdue = max(0, floor((strtotime('now') - strtotime($due_date)) / (60 * 60 * 24)));
                                        echo $days_overdue > 0 ? "<span class='text-danger'>$days_overdue days</span>" : "-";
                                    ?>
                                </td>
                                <td>
                                    <?php if ($issue['fine_amount'] > 0): ?>
                                        <span class="text-danger">
                                            <?php echo number_format($issue['fine_amount'], 2); ?>
                                            <?php echo $issue['paid'] ? ' (Paid)' : ' (Unpaid)'; ?>
                                        </span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($issue['fine_amount'] > 0 && !$issue['paid']): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="fine_id" value="<?php echo $issue['fine_id']; ?>">
                                            <button type="submit" name="mark_paid" class="btn btn-success btn-sm">Mark as Paid</button>
                                        </form>
                                    <?php endif; ?>
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