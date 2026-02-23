CREATE DATABASE library_db;
USE library_db;

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'teacher') NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    status ENUM('approved', 'pending', 'rejected') NOT NULL DEFAULT 'pending'
);

-- Create books table
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    isbn VARCHAR(13) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    added_by INT,
    FOREIGN KEY (added_by) REFERENCES users(id)
);

-- Create book_issues table
CREATE TABLE book_issues (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    student_id INT NOT NULL,
    issue_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    return_date DATE NOT NULL,
    actual_return_date DATETIME DEFAULT NULL,
    status ENUM('issued', 'returned') DEFAULT 'issued',
    reissued BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (book_id) REFERENCES books(id),
    FOREIGN KEY (student_id) REFERENCES users(id)
);

-- Create fines table
CREATE TABLE fines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    issue_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    paid BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (issue_id) REFERENCES book_issues(id)
);

-- Create contact table
CREATE TABLE contact (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    curdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert a default teacher account (optional)
-- Password is 'admin123' (hashed)
INSERT INTO users (username, email, password, role, first_name, last_name, status) 
VALUES (
    'teacher1', 
    'teacher@library.com', 
    '$2y$10$8s7PQqM7T8B/6Wn3Mh.pweSXqyNvN/y0m8RxqDvXzqzp/lRVShoK6', 
    'teacher', 
    'Teacher', 
    'Admin', 
    'approved'
);

-- Insert some sample books (optional)
INSERT INTO books (title, author, isbn, quantity) VALUES
('To Kill a Mockingbird', 'Harper Lee', '9780446310789', 5),
('1984', 'George Orwell', '9780451524935', 3),
('Pride and Prejudice', 'Jane Austen', '9780141439518', 4),
('The Great Gatsby', 'F. Scott Fitzgerald', '9780743273565', 6);