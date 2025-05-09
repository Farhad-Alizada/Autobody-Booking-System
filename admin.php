<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Admin') {
    header('Location: login.html');
    exit();
}


// 1) Fetch current admin info
$stmt = $pdo->prepare("
    SELECT FirstName, LastName, Email, PhoneNumber
    FROM users
    WHERE UserID = ?
");
$stmt->execute([ $_SESSION['user']['UserID'] ]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC)
       ?: ['FirstName'=>'','LastName'=>'','Email'=>'','PhoneNumber'=>''];


// 2) Fetch all services, offerings, coupons & employees
$serviceOfferings = $pdo
    ->query("SELECT OfferingID, OfferingName FROM serviceoffering ORDER BY OfferingName")
    ->fetchAll(PDO::FETCH_ASSOC);

$services = $pdo
    ->query("SELECT OfferingID, OfferingName, ServiceDescription, MinPrice, MaxPrice, ImagePath
             FROM serviceoffering
             ORDER BY OfferingID DESC")
    ->fetchAll(PDO::FETCH_ASSOC);

$coupons = $pdo
    ->prepare("
      SELECT DC.CouponNumber, DC.DiscountAmount, SO.OfferingName
      FROM discountcoupon DC
      JOIN serviceoffering SO USING(OfferingID)
      ORDER BY DC.CouponNumber
    ");
$coupons->execute();
$coupons = $coupons->fetchAll(PDO::FETCH_ASSOC);

$employees = $pdo
    ->query("
      SELECT U.UserID,
             CONCAT(U.FirstName,' ',U.LastName) AS name,
             U.Email,
             U.PhoneNumber,
             E.Address,
             E.JobTitle,
             E.Specialization,
             COUNT(SE.EmployeeUserID) AS jobs
      FROM employee E
      JOIN users U          ON E.UserID = U.UserID
      LEFT JOIN scheduleemployee SE 
        ON E.UserID = SE.EmployeeUserID
      GROUP BY E.UserID
    ")
    ->fetchAll(PDO::FETCH_ASSOC);


// 3) Helper to pull appointments by status
function fetchAppointments($pdo, $status) {
    $sql = "
      SELECT
        S.ScheduleID,
        C.UserID            AS CustomerID,
        CONCAT(CU.FirstName,' ',CU.LastName) AS CustomerName,
        CU.Email            AS CustomerEmail,
        CU.PhoneNumber      AS CustomerPhone,
        C.Address           AS CustomerAddress,
        SO.OfferingName     AS Service,
        V.Make, V.Model, V.Year, V.VINNumber,
        EU.UserID           AS EmployeeID,
        CONCAT(EU.FirstName,' ',EU.LastName) AS EmployeeName,
        S.StartDate,
        S.EndDate
      FROM Schedule S
      JOIN serviceoffering SO ON S.OfferingID = SO.OfferingID
      JOIN customer C         ON S.CustomerUserID = C.UserID
      JOIN users CU           ON C.UserID         = CU.UserID
      LEFT JOIN Vehicle V     ON S.VehicleID      = V.VehicleID
      LEFT JOIN scheduleemployee SE
        ON S.CustomerUserID = SE.CustomerUserID
       AND S.OfferingID     = SE.OfferingID
       AND S.StartDate      = SE.StartDate
       AND S.EndDate        = SE.EndDate
      LEFT JOIN users EU     ON SE.EmployeeUserID = EU.UserID
      WHERE S.Status = ?
      ORDER BY S.StartDate DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$status]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$sections = [
  'Scheduled'   => fetchAppointments($pdo,'Scheduled'),
  'In Progress' => fetchAppointments($pdo,'In Progress'),
  'Completed'   => fetchAppointments($pdo,'Completed'),
];


// 4) Pull every employee and group them by their Specialization

$allEmps = [];
$emps = $pdo->query("
  SELECT 
    U.UserID, 
    CONCAT(U.FirstName,' ',U.LastName) AS Name, 
    E.Specialization
  FROM employee E
  JOIN users U ON E.UserID = U.UserID
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($emps as $e) {
    $allEmps[ $e['Specialization'] ][] = [
      'UserID' => $e['UserID'],
      'Name'   => $e['Name']
    ];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>WrapLab Admin Dashboard</title>
  <link 
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" 
    rel="stylesheet"
  >
  <link 
    href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" 
    rel="stylesheet"
  >
  <link rel="stylesheet" href="styles.css">
</head>
<body class="d-flex">

  <!-- SIDEBAR -->
  <nav id="sidebar" class="bg-black text-white p-3">
    <h3 class="text-purple mb-4">Admin Dashboard</h3>
    <ul class="nav flex-column">
      <li class="nav-item mb-2"><a class="nav-link text-white" href="#my-profile">My Profile</a></li>
      <li class="nav-item mb-2"><a class="nav-link text-white" href="#appointments-history">Appointments</a></li>
      <li class="nav-item mb-2"><a class="nav-link text-white" href="#add-services">Add Services</a></li>
      <li class="nav-item mb-2"><a class="nav-link text-white" href="#coupon-panel">Coupons</a></li>
      <li class="nav-item mb-2"><a class="nav-link text-white" href="#assign-employees">Employees</a></li>
      <li class="nav-item mt-4"><a class="nav-link text-purple" href="login.php">Log out</a></li>
    </ul>
  </nav>

  <!-- MAIN CONTENT  -->
  <div class="flex-grow-1">
    <div class="container-fluid p-4">
      <div class="row">

        <!-- PROFILE & CHANGE PASSWORD  -->
        <div class="col-lg-3 mb-5">
          <section id="my-profile">
            <h3 class="mb-3">My Profile</h3>
            <div class="card mb-4">
              <div class="card-body">
                <p><strong>Name:</strong> <?= htmlspecialchars("{$admin['FirstName']} {$admin['LastName']}") ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($admin['Email']) ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($admin['PhoneNumber']) ?></p>
              </div>
            </div>

            <h4 class="mb-3">Change Password</h4>
            <form id="password-form" action="update_password.php" method="POST" class="row g-3">
              <div class="col-12">
                <label for="new_pwd" class="form-label">New Password</label>
                <input 
                  type="password" 
                  id="new_pwd" 
                  name="new_password"
                  class="form-control" 
                  minlength="6" 
                  required
                >
              </div>
              <div class="col-12">
                <label for="confirm_pwd" class="form-label">Confirm Password</label>
                <input 
                  type="password" 
                  id="confirm_pwd" 
                  name="confirm_password"
                  class="form-control" 
                  minlength="6" 
                  required
                >
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-primary">Update Password</button>
              </div>
            </form>
          </section>
        </div>

        <!-- APPOINTMENTS HISTORY -->
        <div class="col-lg-9">
          <section id="appointments-history" class="mb-5">
            <h3 class="mb-4">Appointments History</h3>
            <?php foreach ($sections as $status => $appts): ?>
              <h5><?=htmlspecialchars($status)?></h5>
              <table class="table table-sm table-bordered mb-4">
                <thead class="table-light">
                  <tr>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Service</th>
                    <th>Vehicle</th>
                    <th>Employee</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($appts)): ?>
                    <tr><td colspan="10" class="text-center">No <?=strtolower($status)?> appointments.</td></tr>
                  <?php else: foreach ($appts as $a): ?>
                    <tr>
                      <td><?=htmlspecialchars($a['CustomerName'])?></td>
                      <td><?=htmlspecialchars($a['CustomerEmail'])?></td>
                      <td><?=htmlspecialchars($a['CustomerPhone'])?></td>
                      <td><?=htmlspecialchars($a['CustomerAddress'])?></td>
                      <td><?=htmlspecialchars($a['Service'])?></td>
                      <td>
                        <?=htmlspecialchars("{$a['Make']} {$a['Model']} ({$a['Year']})")?><br>
                        VIN: <?=htmlspecialchars($a['VINNumber'] ?? 'N/A')?>
                      </td>
                      <td><?=htmlspecialchars($a['EmployeeName'] ?? 'Unassigned')?></td>
                      <td><?=date('Y-m-d H:i',strtotime($a['StartDate']))?></td>
                      <td><?=date('Y-m-d H:i',strtotime($a['EndDate']))?></td>
                      <td class="text-nowrap">
                        <!-- Delete -->
                        <form action="delete_appointment.php" method="POST" class="d-inline">
                          <input type="hidden" name="schedule_id" value="<?=$a['ScheduleID']?>">
                          <button 
                            class="btn btn-sm btn-outline-danger"
                            onclick="return confirm('Delete this appointment?')"
                          >Delete</button>
                        </form>

                        <!-- Reassign -->
                        <?php 
                          $spec = $a['Service'];
                          $candidates = $allEmps[$spec] ?? [];
                          if ($candidates):
                        ?>
                          <form action="reassign_appointment.php" method="POST" class="d-inline">
                            <input type="hidden" name="schedule_id" value="<?=$a['ScheduleID']?>">
                            <select 
                              name="new_employee_id" 
                              class="form-select form-select-sm d-inline w-auto"
                            >
                              <?php foreach ($candidates as $emp): ?>
                                <option 
                                  value="<?=$emp['UserID']?>"
                                  <?= $emp['UserID']==$a['EmployeeID']?'selected':''?>
                                ><?=htmlspecialchars($emp['Name'])?></option>
                              <?php endforeach; ?>
                            </select>
                            <button class="btn btn-sm btn-outline-primary">Reassign</button>
                          </form>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; endif; ?>
                </tbody>
              </table>
            <?php endforeach; ?>
          </section>

          <!-- ADD SERVICE -->
          <section id="add-services" class="mb-5">
            <h3 class="mb-4">Add New Service</h3>
            <form
              id="service-form"
              action="add_service.php"
              method="POST"
              enctype="multipart/form-data"
              class="row g-3"
            >
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
                <input
                  id="minPrice"
                  name="minPrice"
                  type="number"
                  step="0.01"
                  class="form-control"
                  required
                >
              </div>
              <div class="col-md-2">
                <label class="form-label">Max Price ($)</label>
                <input
                  id="maxPrice"
                  name="maxPrice"
                  type="number"
                  step="0.01"
                  class="form-control"
                  required
                >
              </div>
              <div class="col-md-3">
                <label class="form-label">Image</label>
                <input
                  name="serviceImage"
                  type="file"
                  accept="image/*"
                  class="form-control"
                >
              </div>
              <div class="col-12 text-end">
                <button class="btn btn-primary">Add Service</button>
              </div>
            </form>
          </section>

          <!-- ALL SERVICES TABLE -->
          <h5 class="mt-4 mb-2">All Services</h5>
          <table class="table table-striped align-middle mb-5">
            <thead>
              <tr>
                <th>Img</th><th>Name</th><th>Description</th><th>Price Range</th><th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($services as $s): ?>
                <tr>
                  <td>
                    <?php if ($s['ImagePath']): ?>
                      <img
                        src="<?=htmlspecialchars($s['ImagePath'])?>"
                        class="img-fluid"
                        style="max-height:60px"
                      >
                    <?php endif; ?>
                  </td>
                  <td><?=htmlspecialchars($s['OfferingName'])?></td>
                  <td><?=htmlspecialchars($s['ServiceDescription'])?></td>
                  <td>
                    $<?=number_format($s['MinPrice'],2)?> – $<?=number_format($s['MaxPrice'],2)?>
                  </td>
                  <td class="text-end">
                    <a
                      href="edit_service.php?id=<?=$s['OfferingID']?>"
                      class="btn btn-sm btn-outline-secondary me-1"
                    >Edit</a>
                    <form action="delete_service.php" method="POST" class="d-inline">
                      <input type="hidden" name="offering_id" value="<?=$s['OfferingID']?>">
                      <button
                        class="btn btn-sm btn-outline-danger"
                        onclick="return confirm('Delete this service?')"
                      >Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

          <!-- COUPON PANEL -->
          <section id="coupon-panel" class="mb-5">
            <h3 class="mb-4">Manage Discount Coupons</h3>
            <form
              id="coupon-form"
              action="add_coupon.php"
              method="POST"
              class="row g-3 mb-4"
            >
              <div class="col-md-3">
                <label class="form-label">Discount Amount ($)</label>
                <input
                  id="discountAmt"
                  name="amount"
                  type="number"
                  step="0.01"
                  class="form-control"
                  required
                >
              </div>
              <div class="col-md-4">
                <label class="form-label">Service Offering</label>
                <select
                  id="couponSvc"
                  name="offering_id"
                  class="form-select"
                  required
                >
                  <option value="">Choose one…</option>
                  <?php foreach ($services as $svc): ?>
                    <option
                      value="<?=$svc['OfferingID']?>"
                      data-max="<?=$svc['MaxPrice']?>"
                    >
                      <?=htmlspecialchars($svc['OfferingName'])?> 
                      (max $<?=number_format($svc['MaxPrice'],2)?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <input type="hidden" name="admin_id" value="<?=$_SESSION['user']['UserID']?>">
              <div class="col-md-2 align-self-end">
                <button class="btn btn-primary w-100">Add Coupon</button>
              </div>
            </form>

            <table class="table table-striped align-middle mb-5">
              <thead>
                <tr>
                  <th>Coupon #</th><th>Amount</th><th>Offering</th><th></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($coupons as $c): ?>
                  <tr>
                    <td><?=$c['CouponNumber']?></td>
                    <td>$<?=number_format($c['DiscountAmount'],2)?></td>
                    <td><?=htmlspecialchars($c['OfferingName'])?></td>
                    <td class="text-end">
                      <a
                        href="edit_coupon.php?id=<?=$c['CouponNumber']?>"
                        class="btn btn-sm btn-outline-secondary me-1"
                      >Edit</a>
                      <form action="delete_coupon.php" method="POST" class="d-inline">
                        <input type="hidden" name="coupon_number" value="<?=$c['CouponNumber']?>">
                        <button
                          class="btn btn-sm btn-outline-danger"
                          onclick="return confirm('Delete this coupon?')"
                        >Delete</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </section>

          <!-- EMPLOYEES TABLE -->
          <section id="assign-employees" class="mb-5">
            <h3 class="mb-4">Employees</h3>
            <table class="table table-striped align-middle">
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Job</th>
                <th>Specialization</th>
                <th>Jobs</th>
                <th></th>
              </tr>
            </thead>
              <tbody>
                <?php foreach ($employees as $e): ?>
                  <tr>
                    <td><?=htmlspecialchars($e['name'])?></td>
                    <td><?=htmlspecialchars($e['Email'])?></td>
                    <td><?= htmlspecialchars($e['PhoneNumber']) ?></td>
                    <td><?=htmlspecialchars($e['Address'])?></td>
                    <td><?=htmlspecialchars($e['JobTitle'])?></td>
                    <td><?=htmlspecialchars($e['Specialization'])?></td>
                    <td><?=$e['jobs']?></td>
                    <td class="text-end">
                      <a
                        href="edit_employee.php?id=<?=$e['UserID']?>"
                        class="btn btn-sm btn-outline-secondary me-1"
                      >Edit</a>
                      <form action="delete_employee.php" method="POST" class="d-inline">
                        <input type="hidden" name="user_id" value="<?=$e['UserID']?>">
                        <button
                          class="btn btn-sm btn-outline-danger"
                          onclick="return confirm('Delete this employee?')"
                        >Delete</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </section>

          <!-- ADD NEW EMPLOYEE -->
          <section id="add-employee" class="mb-5">
            <h3 class="mb-4">Add New Employee</h3>
            <form
              action="add_employee.php"
              method="POST"
              class="row g-3"
              style="max-width:800px;"
            >
              <div class="col-md-4">
                <label class="form-label">First Name</label>
                <input name="first_name" class="form-control" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Last Name</label>
                <input name="last_name" class="form-control" required>
              </div>
              <div class="col-md-4">
              <label class="form-label">Phone Number</label>
              <input 
                name="phone_number" 
                type="text" 
                class="form-control" 
                maxlength="12" 
                required
              >
            </div>
              <div class="col-md-4">
                <label class="form-label">Address</label>
                <input name="address" class="form-control" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Job Title</label>
                <input name="job_title" class="form-control" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Specialization</label>
                <select name="specialization" class="form-select" required>
                  <option value="">Choose one…</option>
                  <?php foreach ($serviceOfferings as $svc): ?>
                    <option value="<?=htmlspecialchars($svc['OfferingName'])?>">
                      <?=htmlspecialchars($svc['OfferingName'])?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary">Add Employee</button>
              </div>
            </form>
          </section>

        </div><!-- /.col-lg-9 -->

      </div><!-- /.row -->
    </div><!-- /.container-fluid -->
  </div><!-- /.flex-grow-1 -->

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    

    // ── Password mismatch alert ────────────────────────────────────────────────
    document.getElementById('password-form').addEventListener('submit', function(e) {
      const newPwd     = document.getElementById('new_pwd').value.trim();
      const confirmPwd = document.getElementById('confirm_pwd').value.trim();
      if (newPwd !== confirmPwd) {
        e.preventDefault();
        alert(`Passwords do not match❗`);
        document.getElementById('confirm_pwd').focus();
      }
    });
    // Service min ≤ max
    document.getElementById('service-form').addEventListener('submit', function(e) {
      const min = parseFloat(document.getElementById('minPrice').value) || 0;
      const max = parseFloat(document.getElementById('maxPrice').value) || 0;
      if (min > max) {
        e.preventDefault();
        alert(`Minimum price $${min.toFixed(2)} cannot exceed maximum price $${max.toFixed(2)}.❗`);
        document.getElementById('minPrice').focus();
      }
    });

    // Coupon ≤ service max
    document.getElementById('coupon-form').addEventListener('submit', function(e) {
      const amt = parseFloat(document.getElementById('discountAmt').value) || 0;
      const sel = document.getElementById('couponSvc');
      const max = parseFloat(sel.selectedOptions[0].dataset.max) || 0;
      if (amt > max) {
        e.preventDefault();
        alert(`Discount ($${amt.toFixed(2)}) cannot exceed service max price of $${max.toFixed(2)}.❗`);
        document.getElementById('discountAmt').focus();
      }
    });
 
    /* ── phone number must be 10 digits ─────────────────────────── */
  document.querySelector('form[action="add_employee.php"]')
          .addEventListener('submit', function (e) {
    const raw    = this.phone_number.value.trim();          // whatever user typed
    const digits = raw.replace(/\D/g, '');                  // strip non‑digits
    if (digits.length !== 10) {
      e.preventDefault();                                   // cancel submit
      alert('Phone number must contain exactly 10 digits (e.g. 825‑111‑2222 or 8251112222)❗');
      this.phone_number.focus();
    }
  });
</script>
</body>
</html>
