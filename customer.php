<?php
session_start();
require_once 'db_connect.php';

// only customers
if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Customer') {
    header('Location: login.html');
    exit();
}
$customerID = $_SESSION['user']['UserID'];

// ——— Handle profile update ———
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_profile'])) {
    $pref = $_POST['preferred_contact'];
    $addr = trim($_POST['address']);
    $stmt = $pdo->prepare("
      UPDATE Customer
         SET PreferredContact = :pref
           , Address          = :addr
       WHERE UserID = :uid
    ");
    $stmt->execute([':pref'=>$pref,':addr'=>$addr,':uid'=>$customerID]);
    header('Location: customer.php?msg=profile_updated');
    exit();
}

// ——— Fetch profile info ———
$stmt = $pdo->prepare("
  SELECT 
    U.FirstName, U.LastName, U.Email, U.PhoneNumber,
    C.PreferredContact, C.Address
  FROM Users U
  JOIN Customer C ON U.UserID=C.UserID
  WHERE U.UserID = ?
");
$stmt->execute([$customerID]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC)
         ?: ['FirstName'=>'','LastName'=>'','Email'=>'','PhoneNumber'=>'','PreferredContact'=>'','Address'=>''];

// ——— Fetch service offerings ———
$offerings = $pdo->query("SELECT OfferingID, OfferingName FROM ServiceOffering")
                 ->fetchAll(PDO::FETCH_ASSOC);

// ——— Fetch all employees & slots for booking below ———
$empRows = $pdo->query("
  SELECT U.UserID,U.FirstName,U.LastName,E.Specialization
    FROM Users U
    JOIN Employee E ON U.UserID=E.UserID
   WHERE U.AccessLevel='Employee'
")->fetchAll(PDO::FETCH_ASSOC);

$allSlots = $pdo->prepare("
  SELECT EmployeeUserID, AvailabilityDate, StartTime, EndTime
    FROM EmployeeAvailability
   WHERE AvailabilityDate >= CURDATE()
   ORDER BY AvailabilityDate, StartTime
");
$allSlots->execute();
$allSlots = $allSlots->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>WrapLab Customer Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body class="d-flex">

  <!-- SIDEBAR -->
  <nav id="sidebar" class="bg-black text-white p-3">
    <h3 class="text-purple mb-4">Customer Dashboard</h3>
    <ul class="nav flex-column">
      <li class="nav-item mb-2"><a href="#book-service" class="nav-link text-white">Book a Service</a></li>
      <li class="nav-item mb-2"><a href="#my-appointments" class="nav-link text-white">My Appointments</a></li>
      <li class="nav-item mb-2"><a href="#feedback" class="nav-link text-white">Leave Feedback</a></li>
      <li class="nav-item mt-4"><a href="login.php" class="nav-link text-purple">Log out</a></li>
    </ul>
  </nav>

  <!-- MAIN CONTENT -->
  <div class="flex-grow-1">
    <div class="container-fluid p-4">
      <div class="row">

        <!-- PROFILE & CHANGE PASSWORD (30%) -->
        <div class="col-lg-3 mb-5">
          <section id="my-profile">
            <h3 class="mb-3">My Profile</h3>
            <div class="card mb-4">
              <div class="card-body">
                <p><strong>Name:</strong> <?=htmlspecialchars($profile['FirstName'].' '.$profile['LastName'])?></p>
                <p><strong>Email:</strong> <?=htmlspecialchars($profile['Email'])?></p>
                <p><strong>Phone:</strong> <?=htmlspecialchars($profile['PhoneNumber'])?></p>
                <p><strong>Preferred Contact:</strong> <?=htmlspecialchars($profile['PreferredContact'])?></p>
                <p><strong>Address:</strong> <?=htmlspecialchars($profile['Address'])?></p>
              </div>
            </div>

            <!-- update preferred contact & address -->
            <h4 class="mb-3">Edit Contact Info</h4>
            <form action="customer.php" method="POST" class="mb-4">
              <input type="hidden" name="update_profile" value="1">
              <div class="mb-2">
                <label class="form-label">Preferred Contact</label>
                <select name="preferred_contact" class="form-select" required>
                  <option <?= $profile['PreferredContact']==='Email' ? 'selected':''?>>Email</option>
                  <option <?= $profile['PreferredContact']==='Phone' ? 'selected':''?>>Phone</option>
                </select>
              </div>
              <div class="mb-2">
                <label class="form-label">Address</label>
                <input
                  type="text"
                  name="address"
                  class="form-control"
                  value="<?=htmlspecialchars($profile['Address'])?>"
                  required>
              </div>
              <button class="btn btn-primary btn-sm">Save</button>
            </form>

            <!-- change password -->
            <h4 class="mb-3">Change Password</h4>
            <form id="password-form" action="update_password_customer.php" method="POST" class="row g-3">
              <div class="col-12">
                <label for="new_pwd" class="form-label">New Password</label>
                <input type="password" id="new_pwd" name="new_password" class="form-control" minlength="6" required>
              </div>
              <div class="col-12">
                <label for="confirm_pwd" class="form-label">Confirm Password</label>
                <input type="password" id="confirm_pwd" name="confirm_password" class="form-control" minlength="6" required>
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-primary btn-sm">Change Password</button>
              </div>
            </form>
          </section>
        </div>

        <!-- REST OF DASHBOARD (70%) -->
        <div class="col-lg-9">
  <!-- MAIN CONTENT -->
  <div class="flex-grow-1">
    <div class="container-fluid p-4">
      <!-- Book a Service -->
      <section id="book-service" class="mb-5">
        <h3 class="mb-4">Book a Service</h3>
        <form action="book_service.php" method="POST">
          <!-- Service Select -->
          <div class="mb-3">
            <label for="serviceSelect" class="form-label">Select Service</label>
            <select id="serviceSelect" name="service_id" class="form-select" required>
              <option value="">Choose a service...</option>
              <?php foreach ($offerings as $off): ?>
                <option value="<?= $off['OfferingID'] ?>"><?=""?>
                  <?= htmlspecialchars($off['OfferingName']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Vehicle Information -->
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="vehicleMake" class="form-label">Vehicle Make</label>
              <input type="text" id="vehicleMake" name="vehicle_make" class="form-control" placeholder="e.g., Toyota" required>
            </div>
            <div class="col-md-6">
              <label for="vehicleModel" class="form-label">Vehicle Model</label>
              <input type="text" id="vehicleModel" name="vehicle_model" class="form-control" placeholder="e.g., Corolla" required>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-4">
              <label for="vehicleYear" class="form-label">Vehicle Year</label>
              <input type="number" id="vehicleYear" name="vehicle_year" class="form-control" placeholder="e.g., 2021" required>
            </div>
            <div class="col-md-8">
              <label for="vinNumber" class="form-label">VIN Number (Optional)</label>
              <input type="text" id="vinNumber" name="vin_number" class="form-control" placeholder="e.g., 1HGCM82633A123456">
            </div>
          </div>

          <!-- Date / Time / Employee -->
          <div class="row mb-3">
            <div class="col-md-4">
              <label for="serviceDate" class="form-label">Select Date</label>
              <input type="date" id="serviceDate" name="service_date" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label for="timeSelect" class="form-label">Select Time</label>
              <select id="timeSelect" name="service_time" class="form-select" disabled required>
                <option value="">Pick a date first…</option>
              </select>
            </div>
            <div class="col-md-4">
              <label for="employeeSelect" class="form-label">Preferred Employee</label>
              <select id="employeeSelect" name="employee_id" class="form-select">
              <option value="">No preference</option>
  <?php foreach ($empRows as $e): ?>
    <option
      value="<?= $e['UserID'] ?>"
      data-specialization="<?= htmlspecialchars($e['Specialization']) ?>"
    >
      <?= htmlspecialchars($e['FirstName'].' '.$e['LastName']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Book Now</button>
        </form>
      </section>

      <!-- My Appointments -->
      <section id="my-appointments" class="mb-5">
        <h3 class="mb-4">My Appointments</h3>
        <div class="row" id="appointments-container"></div>
      </section>

      <!-- Leave Feedback -->
      <section id="feedback" class="mb-5">
        <h3 class="mb-4">Leave Feedback</h3>
        <form action="submit_feedback.php" method="POST">
          <div class="mb-3">
            <label for="feedbackName" class="form-label">Your Name</label>
            <input type="text" id="feedbackName" name="feedbackName" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="feedbackText" class="form-label">Your Feedback</label>
            <textarea id="feedbackText" name="feedback" rows="3" class="form-control" required></textarea>
          </div>
          <div class="mb-3">
            <label for="rating" class="form-label">Rating</label>
            <select id="rating" name="rating" class="form-select" required>
              <option value="">Choose a rating...</option>
              <?php for ($i=1; $i<=5; $i++): ?>
                <option value="<?= $i ?>"><?=""?>
                  <?= $i ?> star<?= $i>1?'s':'' ?>
                </option>
              <?php endfor; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-primary">Submit Feedback</button>
        </form>
      </section>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Appointments Loader -->
  <script>
    window.addEventListener('DOMContentLoaded', () => {
      fetch('appointments.php')
        .then(r => r.text())
        .then(html => document.getElementById('appointments-container').innerHTML = html);
    });
  </script>
  <!-- Availability & Employee Filter -->
  <script>
    const rawSlots     = <?= json_encode($allSlots) ?>;
    const slotsByDate  = rawSlots.reduce((a,s) => {
      (a[s.AvailabilityDate] = a[s.AvailabilityDate]||[]).push(s);
      return a;
    }, {});
    const dateInput    = document.getElementById('serviceDate');
    const timeSelect   = document.getElementById('timeSelect');
    const employeeSel  = document.getElementById('employeeSelect');

    // store original employee options
    const origEmps = Array.from(employeeSel.options);

    dateInput.addEventListener('change', () => {
      const day = dateInput.value;
      const slots = slotsByDate[day]||[];
      timeSelect.innerHTML = '';
      if (!slots.length) {
        timeSelect.disabled = true;
        timeSelect.innerHTML = '<option>No slots</option>';
        return;
      }
      timeSelect.disabled = false;
      const seen = new Set();
      slots.forEach(s => {
        const t0 = s.StartTime.slice(0,5);
        const t1 = s.EndTime.slice(0,5);
        const label = `${t0} – ${t1}`;
        if (!seen.has(label)) {
          seen.add(label);
          const opt = document.createElement('option');
          const emps = slots
            .filter(x => x.StartTime.slice(0,5)===t0)
            .map(x => x.EmployeeUserID);
          opt.value = t0;
          opt.text  = label;
          opt.dataset.emps = emps.join(',');
          timeSelect.append(opt);
        }
      });
      filterEmployees();
    });

    timeSelect.addEventListener('change', filterEmployees);

    function filterEmployees() {
      const chosen = timeSelect.selectedOptions[0]?.dataset.emps?.split(',')||[];
      employeeSel.innerHTML = '';
      // always include the no-pref option
      employeeSel.append(origEmps[0].cloneNode(true));
      origEmps.slice(1).forEach(opt => {
        if (chosen.length === 0 || chosen.includes(opt.value)) {
          employeeSel.append(opt.cloneNode(true));
        }
      });
    }
  </script>

  <!-- Specialization Filter -->
  <script>
    const serviceSelect  = document.getElementById('serviceSelect');
    const employeeSelect = document.getElementById('employeeSelect');
    const origEmpOpts    = Array.from(employeeSelect.options);

    serviceSelect.addEventListener('change', () => {
      const chosenService = serviceSelect.selectedOptions[0]?.textContent;
      employeeSelect.innerHTML = '';
      // always re‑add “No preference”
      employeeSelect.append(origEmpOpts[0].cloneNode(true));
      // then re‑add only those matching the chosen service
      origEmpOpts.slice(1).forEach(opt => {
        if (!chosenService || opt.dataset.specialization === chosenService) {
          employeeSelect.append(opt.cloneNode(true));
        }
      });
    });

    <!-- JS for password form -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  // ── Password mismatch & length check ────────────────────────────────────────
    document.getElementById('password-form')?.addEventListener('submit', function(e) {
      const n = document.getElementById('new_pwd').value.trim();
      const c = document.getElementById('confirm_pwd').value.trim();
      if (!n || !c) {
        e.preventDefault();
        alert("❗ Both password fields are required.");
      } else if (n.length < 6) {
        e.preventDefault();
        alert("❗ Password must be at least 6 characters long.");
      } else if (n !== c) {
        e.preventDefault();
        alert("❗ Passwords do not match.");
        document.getElementById('confirm_pwd').focus();
      }
    });
  </script>
</body>
</html>
