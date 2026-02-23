Paste the Library file you downloaded here C:\xampp\htdocs

# Library Management System

## Overview
This project is a Library Management System built using PHP and MySQL. It allows users to register as students or teachers, manage their accounts, and handle library operations.

## Prerequisites
Before you begin, ensure you have the following installed on your machine:
- [XAMPP](https://www.apachefriends.org/index.html) (includes Apache and MySQL)
- A web browser (e.g., Chrome, Firefox)

## Setting Up XAMPP

1. **Download XAMPP:**
   - Go to the [XAMPP website](https://www.apachefriends.org/index.html) and download the installer for your operating system.

2. **Install XAMPP:**
   - Run the installer and follow the on-screen instructions. Make sure to install Apache and MySQL.

3. **Start XAMPP:**
   - Open the XAMPP Control Panel.
   - Start the Apache and MySQL services by clicking the "Start" buttons next to each service.

## Setting Up the Database

1. **Access phpMyAdmin:**
   - Open your web browser and go to `http://localhost/phpmyadmin`.

2. **Create a Database:**
   - Click on the "Databases" tab.
   - Enter a name for your database (e.g., `library_management`) and click "Create".

3. **Create Tables:**
   - Use the following SQL commands to create the necessary tables. Click on the "SQL" tab in phpMyAdmin and run the following queries:

   ```sql
   CREATE TABLE users (
       id INT AUTO_INCREMENT PRIMARY KEY,
       username VARCHAR(50) NOT NULL UNIQUE,
       email VARCHAR(100) NOT NULL UNIQUE,
       password VARCHAR(255) NOT NULL,
       role ENUM('student', 'teacher') NOT NULL,
       first_name VARCHAR(50) NOT NULL,
       last_name VARCHAR(50) NOT NULL,
       status ENUM('approved', 'pending') NOT NULL
   );

   CREATE TABLE teacher_codes (
       id INT AUTO_INCREMENT PRIMARY KEY,
       code VARCHAR(50) NOT NULL UNIQUE,
       is_used BOOLEAN DEFAULT FALSE,
       used_by INT,
       used_at TIMESTAMP NULL
   );
   ```

4. **Configure Database Connection:**
   - Open the `config.php` file in your project directory and update the database connection settings as follows:

   ```php
   <?php
   $servername = "localhost";
   $username = "root"; // default XAMPP username
   $password = ""; // default XAMPP password is empty
   $dbname = "library_management"; // your database name

   // Create connection
   $conn = new mysqli($servername, $username, $password, $dbname);

   // Check connection
   if ($conn->connect_error) {
       die("Connection failed: " . $conn->connect_error);
   }
   ?>
   ```

## Running the Project

1. **Place Project Files:**
   - Copy your project folder (containing `register.php`, `config.php`, and other files) into the `htdocs` directory of your XAMPP installation. This is usually located at `C:\xampp\htdocs\`.

2. **Access the Application:**
   - Open your web browser and go to `http://localhost/your_project_folder/register.php` to access the registration page.

3. **Register an Account:**
   - Fill out the registration form to create a new user account.

## Additional Notes
- Ensure that the Apache and MySQL services are running in the XAMPP Control Panel whenever you want to access the application.
- You can manage your database and tables using phpMyAdmin at `http://localhost/phpmyadmin`.

## Troubleshooting
- If you encounter issues with starting Apache or MySQL, ensure that no other applications are using the same ports (80 for Apache and 3306 for MySQL).
- Check the XAMPP Control Panel for error messages and consult the XAMPP documentation for further assistance.
#To use the file after using the following steps type localhost/Library/ into web browser if you did everything right it should work
## License
This project is open-source and available for personal and educational use.