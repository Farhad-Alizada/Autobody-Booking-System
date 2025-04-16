<?php
session_start();
require_once 'db_connect.php';

// Redirect if not admin
if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Admin') {
  header('Location: login.html');
  exit();
}

// Example dummy values for now (until you plug in real data):
$employees = [
  ['name' => 'John', 'role' => 'Mechanic', 'jobs' => 2],
  ['name' => 'Jane', 'role' => 'Mechanic', 'jobs' => 1],
  ['name' => 'Alex', 'role' => 'Inspector', 'jobs' => 0],
];
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
  <!-- SIDEBAR -->
  <nav id="sidebar" class="bg-black text-white p-3">
    <h3 class="text-purple mb-4">Admin DashBoard</h3>
    <ul class="nav flex-column">
      <li class="nav-item mb-2"><a href="#add-services" class="nav-link text-white">Add Services</a></li>
      <li class="nav-item mb-2"><a href="#search-customers" class="nav-link text-white">Search Customers</a></li>
      <li class="nav-item mb-2"><a href="#create-coupons" class="nav-link text-white">Create Coupons</a></li>
      <li class="nav-item mb-2"><a href="#assign-employees" class="nav-link text-white">Assign Employees</a></li>
      <li class="nav-item mt-4"><a href="login.html" class="nav-link text-purple">Log out</a></li>
    </ul>
  </nav>

  <div class="container-fluid p-4">
    <!-- DASHBOARD -->
    <section id="dashboard" class="mb-5">
      <h3 class="mb-4">Appointments</h3>
      <div class="row">
        <div class="col-md-4 mb-4">
          <div class="card card-unassigned p-3">
            <h5>Vehicle: ABC123 – Toyota Camry</h5>
            <p>Not assigned to any employee.</p>
            <select class="form-select mb-2">
              <option>Select Employee…</option>
              <?php foreach ($employees as $e): ?>
                <option><?= htmlspecialchars($e['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <button class="btn btn-primary">Assign</button>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card card-inprogress p-3">
            <h5>Vehicle: DEF456 – Honda Civic</h5>
            <p>Assigned to Jane.</p>
            <button class="btn btn-secondary">View Details</button>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card card-completed p-3">
            <h5>Vehicle: GHI789 – Ford F‑150</h5>
            <p>Completed by Alex.</p>
            <button class="btn btn-secondary">See Report</button>
          </div>
        </div>
      </div>
    </section>

    <!-- ADD SERVICES -->
    <section id="add-services" class="mb-5">
      <h3 class="mb-4">Add New Service</h3>
      <form class="row g-3" enctype="multipart/form-data" action="#" method="POST">
        <div class="col-md-4">
          <label class="form-label">Service Name</label>
          <input type="text" name="serviceName" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Description</label>
          <textarea name="serviceDesc" class="form-control" rows="1" required></textarea>
        </div>
        <div class="col-md-2">
          <label class="form-label">Price Range</label>
          <input type="text" name="priceRange" class="form-control" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">Upload Image</label>
          <input type="file" name="serviceImage" class="form-control" accept="image/*" required>
        </div>
        <div class="col-12 text-end">
          <button type="submit" class="btn btn-primary">Add Service</button>
        </div>
      </form>
    </section>

    <!-- SEARCH CUSTOMERS -->
    <section id="search-customers" class="mb-5">
      <h3 class="mb-4">Search Customers</h3>
      <div class="input-group mb-3">
        <input type="text" class="form-control" placeholder="Enter customer name or ID">
        <button class="btn btn-primary">Search</button>
      </div>
      <ul class="list-group">
        <li class="list-group-item">John Smith (ID: C123) – johndoe@example.com</li>
        <li class="list-group-item">Jane Doe (ID: C456) – janedoe@example.com</li>
      </ul>
    </section>

    <!-- CREATE COUPONS -->
    <section id="create-coupons" class="mb-5">
      <h3 class="mb-4">Create Coupon Code</h3>
      <form class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Code</label>
          <input type="text" class="form-control" placeholder="e.g. SAVE10">
        </div>
        <div class="col-md-3">
          <label class="form-label">Discount %</label>
          <input type="number" class="form-control" placeholder="10">
        </div>
        <div class="col-md-3">
          <label class="form-label">Expiry Date</label>
          <input type="date" class="form-control">
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <button type="submit" class="btn btn-primary w-100">Create Coupon</button>
        </div>
      </form>
    </section>

    <!-- ASSIGN EMPLOYEES -->
    <section id="assign-employees" class="mb-5">
      <h3 class="mb-4">Assign Employees</h3>
      <table class="table">
        <thead>
          <tr>
            <th>Employee</th>
            <th>Role</th>
            <th>Current Jobs</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($employees as $e): ?>
          <tr>
            <td><?= htmlspecialchars($e['name']) ?></td>
            <td><?= htmlspecialchars($e['role']) ?></td>
            <td><?= $e['jobs'] ?></td>
            <td><button class="btn btn-secondary btn-sm">View & Assign</button></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
