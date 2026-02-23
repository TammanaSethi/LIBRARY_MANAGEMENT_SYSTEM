<?php
session_start();
require_once 'config.php';
require_once 'includes/functions.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Library Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .contact-card {
            border: none;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .contact-card:hover {
            transform: translateY(-5px);
        }
        .contact-header {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            color: white;
            padding: 20px;
            border-radius: 5px 5px 0 0;
        }
        .contact-icon {
            font-size: 2.5rem;
            color: #0d6efd;
            margin-bottom: 15px;
        }
        .contact-info {
            text-align: center;
            padding: 20px;
            margin: 10px 0;
            background-color: #f8f9fa;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .contact-info:hover {
            background-color: #e9ecef;
        }
        .contact-label {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        .contact-value {
            color: #212529;
            font-size: 1.1rem;
            font-weight: 500;
        }
        .contact-value a {
            color: #0d6efd;
            text-decoration: none;
        }
        .contact-value a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Library System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Register</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="contact.php">Contact Us</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card contact-card">
                    <div class="contact-header">
                        <h3 class="text-center mb-0">Contact Us</h3>
                    </div>
                    <div class="card-body py-5">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="contact-info">
                                    <i class="fas fa-envelope contact-icon"></i>
                                    <div class="contact-label">Email Us At</div>
                                    <div class="contact-value">
                                        <a href="mailto:library@gmail.com">library@gmail.com</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="contact-info">
                                    <i class="fas fa-phone contact-icon"></i>
                                    <div class="contact-label">Call Us At</div>
                                    <div class="contact-value">
                                        <a href="tel:+1234567890">+1 (234) 567-890</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-4">
                            <p class="text-muted mb-0">We're here to help! Contact us for any queries about our library services.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>