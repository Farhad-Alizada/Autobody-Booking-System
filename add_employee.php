<?php
session_start();
require_once 'db_connect.php';

// Redirect if not logged in as admin
if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Admin') {
  header('Location: login.html');
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $firstName = $_POST['first_name'];
  $lastName = $_POST['last_name'];
  $email = $_POST['email'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $phone = $_POST['phone'];
  $jobTitle = $_POST['job_title'];
  $specialization = $_POST['specialization'];

  try {
    // Start a transaction
    $pdo->beginTransaction();

    // Insert into Users
    $stmtUser = $pdo->prepare("
      INSERT INTO Users (Password, PhoneNumber, FirstName, LastName, Email, AccessLevel)
      VALUES (:password, :phone, :first_name, :last_name, :email, 'Employee')
    ");
    $stmtUser->execute([
      ':password' => $password,
      ':phone' => $phone,
      ':first_name' => $firstName,
      ':last_name' => $lastName,
      ':email' => $email
    ]);

    $userID = $pdo->lastInsertId();

    // Insert into Employee
    $stmtEmp = $pdo->prepare("
      INSERT INTO Employee (UserID, JobTitle, Specialization)
      VALUES (:user_id, :job_title, :specialization)
    ");
    $stmtEmp->execute([
      ':user_id' => $userID,
      ':job_title' => $jobTitle,
      ':specialization' => $specialization
    ]);

    $pdo->commit();

    // Redirect back to admin dashboard
    header('Location: admin.php?success=1');
    exit();
  } catch (PDOException $e) {
    $pdo->rollBack();
    if ($e->getCode() == 23000) {
        echo "An employee with this email already exists.";
      } else {
        echo "Error adding employee: " . $e->getMessage();
      }
  }
}
?>
