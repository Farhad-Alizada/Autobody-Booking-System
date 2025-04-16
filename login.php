<?php
session_start();
require_once 'db_connect.php';

if (isset($_POST['account_id']) && isset($_POST['password'])) {
    $accountID = $_POST['account_id'];
    $password  = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM Users WHERE Email = :email");
    $stmt->bindValue(':email', $accountID);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // For demo, we assume plain text passwords — in production, use password_hash and password_verify!
        if ($password === $user['Password']) {
            $_SESSION['user'] = $user;

            // Redirect based on role
            switch ($user['AccessLevel']) {
                case 'Admin':
                    header('Location: admin.php');
                    break;
                case 'Employee':
                    header('Location: employee.php');
                    break;
                case 'Customer':
                    header('Location: customer.php');
                    break;
                default:
                    header('Location: login.html?error=unknown_role');
                    break;
            }
            exit();
        } else {
            header('Location: login.html?error=invalid_credentials');
            exit();
        }
    } else {
        header('Location: login.html?error=user_not_found');
        exit();
    }
} else {
    header('Location: login.html?error=missing_fields');
    exit();
}
