<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Admin') {
  header('Location: login.html');
  exit();
}

// Fetch all employees
$employeeStmt = $pdo->prepare("
  SELECT 
    U.UserID,
    CONCAT(U.FirstName, ' ', U.LastName) AS name,
    U.Email,
    U.PhoneNumber,
    E.JobTitle,
    E.Specialization,
    COUNT(SE.EmployeeUserID) AS jobs
  FROM Employee E
  JOIN Users U ON E.UserID = U.UserID
  LEFT JOIN ScheduleEmployee SE ON E.UserID = SE.EmployeeUserID
  GROUP BY E.UserID
");
$employeeStmt->execute();
$employees = $employeeStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all services
$servicesStmt = $pdo->prepare("SELECT OfferingName, ServiceDescription, ServicePrice FROM ServiceOffering");
$servicesStmt->execute();
$services = $servicesStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>WrapLab Admin Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css" />
</head>
<body class="d-flex">
<nav id="sidebar" class="bg-black text-white p-3">
  <h3 class="text-purple mb-4">Admin DashBoard</h3>
  <ul class="nav flex-column">
    <li class="nav-item mb-2"><a href="#add-services" class="nav-link text-white">Add Services</a></li>
    <li class="nav-item mb-2"><a href="#assign-employees" class="nav-link text-white">Assign Employees</a></li>
    <li class="nav-item mt-4"><a href="login.html" class="nav-link text-purple">Log out</a></li>
  </ul>
</nav>

<div class="container-fluid p-4">

  <!-- Add New Service -->
  <section id="add-services" class="mb-5">
    <h3 class="mb-4">Add New Service</h3>
    <form class="row g-3" action="add_service.php" method="POST">
      <div class="col-md-3">
        <label class="form-label">Service Name</label>
        <input type="text" name="serviceName" class="form-control" required>
      </div>
      <div class="col-md-5">
        <label class="form-label">Description</label>
        <input type="text" name="serviceDesc" class="form-control" required>
      </div>
      <div class="col-md-2">
        <label class="form-label">Price</label>
        <input type="number" step="0.01" name="priceRange" class="form-control" required>
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-primary w-100">Add Service</button>
      </div>
    </form>
  </section>

  <!-- All Services Table -->
  <h5 class="mt-4 mb-2">All Services</h5>
  <table class="table">
    <thead>
      <tr>
        <th>Service Name</th>
        <th>Description</th>
        <th>Price</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($services as $s): ?>
        <tr>
          <td><?= htmlspecialchars($s['OfferingName']) ?></td>
          <td><?= htmlspecialchars($s['ServiceDescription']) ?></td>
          <td>$<?= number_format($s['ServicePrice'], 2) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Assign Employees -->
  <section id="assign-employees" class="mb-5">
    <h3 class="mt-5 mb-4">Assign Employees</h3>
    <table class="table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Job Title</th>
          <th>Specialization</th>
          <th>Jobs</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($employees as $e): ?>
          <tr>
            <td><?= htmlspecialchars($e['name']) ?></td>
            <td><?= htmlspecialchars($e['Email']) ?></td>
            <td><?= htmlspecialchars($e['PhoneNumber']) ?></td>
            <td><?= htmlspecialchars($e['JobTitle']) ?></td>
            <td><?= htmlspecialchars($e['Specialization']) ?></td>
            <td><?= $e['jobs'] ?></td>
            <td>
              <button 
                class="btn btn-secondary btn-sm view-employee" 
                data-bs-toggle="modal" 
                data-bs-target="#employeeModal"
                data-name="<?= htmlspecialchars($e['name']) ?>"
                data-email="<?= htmlspecialchars($e['Email']) ?>"
                data-phone="<?= htmlspecialchars($e['PhoneNumber']) ?>"
                data-title="<?= htmlspecialchars($e['JobTitle']) ?>"
                data-spec="<?= htmlspecialchars($e['Specialization']) ?>"
                data-jobs="<?= $e['jobs'] ?>"
              >View & Assign</button>
              <form action="delete_employee.php" method="POST" class="d-inline">
                <input type="hidden" name="user_id" value="<?= $e['UserID'] ?>">
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Add Employee Form -->
    <h4 class="mt-5">Add New Employee</h4>
    <form action="add_employee.php" method="POST" class="row g-3 mb-4">
      <div class="col-md-4">
        <label class="form-label">First Name</label>
        <input type="text" name="first_name" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Last Name</label>
        <input type="text" name="last_name" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Job Title</label>
        <input type="text" name="job_title" class="form-control" required>
      </div>
      <div class="col-md-12">
        <label class="form-label">Specialization</label>
        <input type="text" name="specialization" class="form-control" required>
      </div>
      <div class="col-12 text-end">
        <button type="submit" class="btn btn-primary">Add Employee</button>
      </div>
    </form>
  </section>
</div>

<!-- MODAL -->
<div class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="employeeModalLabel">Employee Info</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>Name:</strong> <span id="empName"></span></p>
        <p><strong>Email:</strong> <span id="empEmail"></span></p>
        <p><strong>Phone:</strong> <span id="empPhone"></span></p>
        <p><strong>Job Title:</strong> <span id="empTitle"></span></p>
        <p><strong>Specialization:</strong> <span id="empSpec"></span></p>
        <p><strong>Jobs Assigned:</strong> <span id="empJobs"></span></p>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll('.view-employee');
    buttons.forEach(button => {
      button.addEventListener('click', function () {
        document.getElementById('empName').textContent = this.getAttribute('data-name');
        document.getElementById('empEmail').textContent = this.getAttribute('data-email');
        document.getElementById('empPhone').textContent = this.getAttribute('data-phone');
        document.getElementById('empTitle').textContent = this.getAttribute('data-title');
        document.getElementById('empSpec').textContent = this.getAttribute('data-spec');
        document.getElementById('empJobs').textContent = this.getAttribute('data-jobs');
      });
    });
  });
</script>
</body>
</html>
