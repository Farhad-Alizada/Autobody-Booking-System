<?php
session_start();
require_once 'db_connect.php';


// Check if form data is set
if (isset($_POST['account_id']) && isset($_POST['password'])) {
    $accountID = $_POST['account_id'];
    $password  = $_POST['password'];

    // Example query if 'account_id' maps to the 'Email' column in your Users table
    // Adjust to your actual column name if it’s different.
    $stmt = $pdo->prepare("SELECT * FROM Users WHERE Email = :email");
    $stmt->bindValue(':email', $accountID);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // echo "<pre>";
    // print_r($user);
    // echo "</pre>";
    // exit();

    if ($user) {
        // Normally you'd store hashed passwords with password_hash() and compare via password_verify().
        // For example:
        // if (password_verify($password, $user['Password'])) { ... } else { ... }
        if ($password === $user['Password']) {
            // Auth success
            $_SESSION['user'] = $user;
            
            // Check role to redirect
            if ($user['AccessLevel'] === 'Admin') {
                header('Location: admin.html');
                exit();
            } elseif ($user['AccessLevel'] === 'Employee') {
                header('Location: employee.html');
                exit();
            } elseif ($user['AccessLevel'] === 'Customer') {
                header('Location: customer.html');
                exit();
            } else {
                // Unknown role
                header('Location: login.html?error=role_not_found');
                exit();
            }
        } else {
            // Wrong password
            header('Location: login.html?error=invalid_credentials');
            exit();
        }
    } else {
        // User not found
        header('Location: login.html?error=user_not_found');
        exit();
    }
} else {
    // Form not submitted properly
    header('Location: login.html');
    exit();
}
