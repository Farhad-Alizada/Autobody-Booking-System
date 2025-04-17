<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Admin') {
  header('Location: login.html');
  exit();
}

// fetch employees
$employees = $pdo->query("
  SELECT U.UserID,
         CONCAT(U.FirstName,' ',U.LastName) AS name,
         U.Email, U.PhoneNumber,
         E.JobTitle, E.Specialization,
         COUNT(SE.EmployeeUserID) AS jobs
  FROM Employee E
  JOIN Users U ON E.UserID = U.UserID
  LEFT JOIN ScheduleEmployee SE ON E.UserID = SE.EmployeeUserID
  GROUP BY E.UserID
")->fetchAll(PDO::FETCH_ASSOC);

// fetch services with Min/Max price
$services = $pdo->query("
  SELECT OfferingID,
         OfferingName,
         ServiceDescription,
         MinPrice,
         MaxPrice,
         ImagePath
  FROM ServiceOffering
  ORDER BY OfferingID DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>WrapLab Admin Dashboard</title>
  <link 
    rel="stylesheet" 
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
  >
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" 
        rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body class="d-flex">

<nav id="sidebar" class="bg-black text-white p-3">
  <h3 class="text-purple mb-4">Admin Dashboard</h3>
  <ul class="nav flex-column">
    <li class="nav-item mb-2"><a class="nav-link text-white" href="#add-services">Add Services</a></li>
    <li class="nav-item mb-2"><a class="nav-link text-white" href="#coupon-panel">Coupons</a></li>
    <li class="nav-item mb-2"><a class="nav-link text-white" href="#assign-employees">Employees</a></li>
    <li class="nav-item mt-4"><a class="nav-link text-purple" href="login.html">Log out</a></li>
  </ul>
</nav>

<div class="container-fluid p-4">
  <!-- ========== ADD SERVICE ========== -->
  <section id="add-services" class="mb-5">
    <h3 class="mb-4">Add New Service</h3>
    <form action="add_service.php" method="POST" enctype="multipart/form-data" class="row g-3">
      <div class="col-md-3">
        <label class="form-label">Service Name</label>
        <input name="serviceName" class="form-control" required>
      </div>
      <div class="col-md-5">
        <label class="form-label">Description</label>
        <input name="serviceDesc" class="form-control" required>
      </div>
      <div class="col-md-2">
        <label class="form-label">Min Price ($)</label>
        <input name="minPrice" type="number" step="0.01" class="form-control" required>
      </div>
      <div class="col-md-2">
        <label class="form-label">Max Price ($)</label>
        <input name="maxPrice" type="number" step="0.01" class="form-control" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Image</label>
        <input name="serviceImage" type="file" accept="image/*" class="form-control">
      </div>
      <div class="col-12 text-end">
        <button class="btn btn-primary">Add Service</button>
      </div>
    </form>
  </section>

  <!-- ========== ALL SERVICES ========== -->
  <h5 class="mt-4 mb-2">All Services</h5>
  <table class="table table-striped align-middle">
    <thead>
      <tr>
        <th style="width:80px">Img</th>
        <th>Name</th>
        <th>Description</th>
        <th>Price Range</th>
        <th style="width:130px"></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($services as $s): ?>
      <tr>
        <td>
          <?php if($s['ImagePath']): ?>
            <img src="<?=htmlspecialchars($s['ImagePath'])?>"
                 style="max-height:60px" class="img-fluid">
          <?php endif; ?>
        </td>
        <td><?=htmlspecialchars($s['OfferingName'])?></td>
        <td><?=htmlspecialchars($s['ServiceDescription'])?></td>
        <td>
          $<?=number_format($s['MinPrice'],2)?> – $<?=number_format($s['MaxPrice'],2)?>
        </td>
        <td class="text-end">
          <a href="edit_service.php?id=<?=$s['OfferingID']?>"
             class="btn btn-sm btn-outline-secondary me-1">Edit</a>
          <form action="delete_service.php" method="POST" class="d-inline">
            <input type="hidden" name="offering_id" value="<?=$s['OfferingID']?>">
            <button class="btn btn-sm btn-outline-danger"
                    onclick="return confirm('Delete this service?')">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- ========== COUPON PANEL ========== -->
  <section id="coupon-panel" class="mt-5">
    <h3 class="mb-4">Manage Discount Coupons</h3>

    <!-- Create Coupon Form -->
    <form id="coupon-form" action="add_coupon.php" method="POST" class="row g-3 mb-4">
      <div class="col-md-3">
        <label class="form-label">Discount Amount ($)</label>
        <input name="amount" type="number" step="0.01" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Service Offering</label>
        <select name="offering_id" class="form-select" required>
          <option value="">Choose one…</option>
          <?php foreach($services as $svc): ?>
            <option value="<?= $svc['OfferingID'] ?>"
                    data-max="<?= $svc['MaxPrice'] ?>">
              <?= htmlspecialchars($svc['OfferingName']) ?>
              (max $<?=number_format($svc['MaxPrice'],2)?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <input type="hidden" name="admin_id" value="<?= $_SESSION['user']['UserID'] ?>">
      <div class="col-md-2 align-self-end">
        <button class="btn btn-primary w-100">Add Coupon</button>
      </div>
    </form>

    <!-- Existing Coupons -->
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Coupon #</th>
          <th>Amount</th>
          <th>Offering</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
          $coupons = $pdo->query("
            SELECT DC.CouponNumber,
                   DC.DiscountAmount,
                   DC.OfferingID,
                   SO.OfferingName,
                   SO.MaxPrice
            FROM DiscountCoupon DC
            JOIN ServiceOffering SO
              ON DC.OfferingID = SO.OfferingID
            WHERE DC.AdminUserID = " . $pdo->quote($_SESSION['user']['UserID'])
          )->fetchAll(PDO::FETCH_ASSOC);

          foreach($coupons as $c): ?>
        <tr>
          <td><?= $c['CouponNumber'] ?></td>
          <td>$<?= number_format($c['DiscountAmount'],2) ?></td>
          <td><?= htmlspecialchars($c['OfferingName']) ?></td>
          <td>
            <a href="edit_coupon.php?id=<?= $c['CouponNumber'] ?>"
               class="btn btn-sm btn-outline-secondary me-1">Edit</a>
            <form action="delete_coupon.php" method="POST" class="d-inline">
              <input type="hidden" name="coupon_number" value="<?= $c['CouponNumber'] ?>">
              <button class="btn btn-sm btn-outline-danger"
                      onclick="return confirm('Delete coupon #<?= $c['CouponNumber'] ?>?')">
                Delete
              </button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </section>

  <!-- ========== EMPLOYEES ========== -->
  <section id="assign-employees" class="mt-5">
    <h3 class="mb-4">Employees</h3>
    <table class="table table-striped">
      <thead><tr>
        <th>Name</th><th>Email</th><th>Phone</th><th>Job</th>
        <th>Specialization</th><th>Jobs</th><th></th>
      </tr></thead>
      <tbody>
      <?php foreach($employees as $e): ?>
      <tr>
        <td><?=htmlspecialchars($e['name'])?></td>
        <td><?=htmlspecialchars($e['Email'])?></td>
        <td><?=htmlspecialchars($e['PhoneNumber'])?></td>
        <td><?=htmlspecialchars($e['JobTitle'])?></td>
        <td><?=htmlspecialchars($e['Specialization'])?></td>
        <td><?=$e['jobs']?></td>
        <td>
          <button class="btn btn-secondary btn-sm view-employee"
                  data-bs-toggle="modal" data-bs-target="#employeeModal"
                  data-name="<?=htmlspecialchars($e['name'])?>"
                  data-email="<?=htmlspecialchars($e['Email'])?>"
                  data-phone="<?=htmlspecialchars($e['PhoneNumber'])?>"
                  data-title="<?=htmlspecialchars($e['JobTitle'])?>"
                  data-spec="<?=htmlspecialchars($e['Specialization'])?>"
                  data-jobs="<?=$e['jobs']?>">
            View
          </button>
          <form action="delete_employee.php" method="POST" class="d-inline">
            <input type="hidden" name="user_id" value="<?=$e['UserID']?>">
            <button class="btn btn-danger btn-sm"
                    onclick="return confirm('Delete employee?')">
              Delete
            </button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </section>
</div>

<!-- Employee Modal -->
<div class="modal fade" id="employeeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title">Employee Info</h5>
      <button class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
      <p><strong>Name:</strong> <span id="empName"></span></p>
      <p><strong>Email:</strong> <span id="empEmail"></span></p>
      <p><strong>Phone:</strong> <span id="empPhone"></span></p>
      <p><strong>Job Title:</strong> <span id="empTitle"></span></p>
      <p><strong>Specialization:</strong> <span id="empSpec"></span></p>
      <p><strong>Jobs Assigned:</strong> <span id="empJobs"></span></p>
    </div>
  </div></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Prevent discount > max price
  document.getElementById('coupon-form').addEventListener('submit', function(e) {
    const amount    = parseFloat(this.amount.value) || 0;
    const sel       = this.offering_id;
    const maxPrice  = parseFloat(sel.selectedOptions[0].dataset.max) || 0;

    if (amount > maxPrice) {
      e.preventDefault();
      alert(
        `❗ Discount $${amount.toFixed(2)} cannot exceed ` +
        `the service’s max price of $${maxPrice.toFixed(2)}.`
      );
      this.amount.focus();
    }
  });

  // Employee modal wiring
  document.querySelectorAll('.view-employee').forEach(btn => {
    btn.addEventListener('click', () => {
      ['Name','Email','Phone','Title','Spec','Jobs'].forEach(f => {
        document.getElementById('emp'+f).textContent =
          btn.dataset[f.toLowerCase()];
      });
    });
  });
</script>
</body>
</html>
