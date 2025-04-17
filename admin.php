<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Admin') {
  header('Location: login.html');
  exit();
}

// Get employees
$stmt = $pdo->prepare("
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
$stmt->execute();
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get services
$serviceStmt = $pdo->prepare("SELECT * FROM ServiceOffering");
$serviceStmt->execute();
$services = $serviceStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>WrapLab Admin Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
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
  <?php if (isset($_GET['success']) && $_GET['success'] === '1'): ?>
    <div class="alert alert-success">Employee added successfully!</div>
  <?php endif; ?>
  <?php if (isset($_GET['service']) && $_GET['service'] === 'added'): ?>
    <div class="alert alert-success">Service added successfully!</div>
  <?php endif; ?>

  <!-- ADD SERVICES -->
  <section id="add-services" class="mb-5">
    <h3 class="mb-4">Add New Service</h3>
    <form class="row g-3" enctype="multipart/form-data" action="add_service.php" method="POST">
      <div class="col-md-4">
        <label class="form-label">Service Name</label>
        <input type="text" name="serviceName" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Description</label>
        <textarea name="serviceDesc" class="form-control" rows="1" required></textarea>
      </div>
      <div class="col-md-2">
        <label class="form-label">Price</label>
        <input type="number" name="servicePrice" class="form-control" step="0.01" required>
      </div>
      <div class="col-md-2">
        <label class="form-label">Upload Image</label>
        <input type="file" name="serviceImage" class="form-control" accept="image/*" required>
      </div>
      <div class="col-12 text-end">
        <button type="submit" class="btn btn-primary">Add Service</button>
      </div>
    </form>

    <!-- Service Display Table -->
    <h5 class="mt-5">All Services</h5>
    <table class="table mt-3">
      <thead>
        <tr>
          <th>Service Name</th>
          <th>Description</th>
          <th>Price</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($services as $service): ?>
        <tr>
          <td><?= htmlspecialchars($service['OfferingName']) ?></td>
          <td><?= htmlspecialchars($service['ServiceDescription']) ?></td>
          <td>$<?= number_format($service['ServicePrice'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </section>

  <!-- ASSIGN EMPLOYEES -->
  <section id="assign-employees" class="mb-5">
    <h3 class="mb-4">Assign Employees</h3>
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
  </section>
</div>

<!-- Employee Info Modal -->
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
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
