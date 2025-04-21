
<?php
session_start();
require_once 'db_connect.php';

// only allow employees
if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Employee') {
    header('Location: login.html');
    exit();
}


$employeeID = $_SESSION['user']['UserID'];

// HANDLE availability deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['availability_id'])) {
    $pdo->prepare("
        DELETE FROM EmployeeAvailability
         WHERE AvailabilityID = :id
           AND EmployeeUserID = :empID
    ")->execute([
        ':id'    => $_POST['availability_id'],
        ':empID' => $employeeID
    ]);
    header("Location: employee.php");
    exit();
}

// HANDLE personal‑info update  (phone + address)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_info'])) {

    $newPhone = trim($_POST['phone']);
    $newAddr  = trim($_POST['address']);

    // Update Users.PhoneNumber
    $pdo->prepare("
        UPDATE Users
           SET PhoneNumber = :phone
         WHERE UserID = :uid
    ")->execute([
        ':phone' => $newPhone,
        ':uid'   => $employeeID
    ]);

    // Update Employee.Address
    $pdo->prepare("
        UPDATE Employee
           SET Address = :addr
         WHERE UserID = :uid
    ")->execute([
        ':addr' => $newAddr,
        ':uid'  => $employeeID
    ]);

    header('Location: employee.php?msg=info_updated');
    exit();
}

// FETCH profile info  (job, specialization & address)
$stmt = $pdo->prepare("
    SELECT U.FirstName, U.LastName, U.Email, U.PhoneNumber,
           E.JobTitle,  E.Specialization, E.Address
      FROM Users    U
      JOIN Employee E ON U.UserID = E.UserID
     WHERE U.UserID = ?
");



$stmt->execute([$employeeID]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC) 
          ?: ['FirstName'=>'','LastName'=>'','Email'=>'','PhoneNumber'=>''];

// Fetch tasks assigned to this employee 
$stmt = $pdo->prepare("
    SELECT 
        S.ScheduleID,
        S.StartDate,
        S.EndDate,
        S.Status,

        -- customer info
        U.FirstName            AS CustomerFirstName,
        U.LastName             AS CustomerLastName,
        U.Email                AS CustomerEmail,
        U.PhoneNumber          AS CustomerPhone,
        C.PreferredContact     AS CustomerPref,

        -- service
        SO.OfferingName,

        -- vehicle (may be NULL)
        V.Make,
        V.Model,
        V.Year,
        V.VINNumber
    FROM Schedule S
    JOIN ScheduleEmployee SE 
      ON S.CustomerUserID = SE.CustomerUserID
     AND S.OfferingID     = SE.OfferingID
     AND S.StartDate      = SE.StartDate
     AND S.EndDate        = SE.EndDate

    JOIN Users U           ON S.CustomerUserID = U.UserID
    JOIN Customer C        ON U.UserID         = C.UserID
    JOIN ServiceOffering SO ON S.OfferingID    = SO.OfferingID

    LEFT JOIN Vehicle V    ON S.VehicleID      = V.VehicleID

    WHERE SE.EmployeeUserID = :emp
    ORDER BY S.StartDate ASC
");
$stmt->execute([':emp' => $employeeID]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Fetch upcoming availabilities 
$avQ = $pdo->prepare("
    SELECT AvailabilityID, AvailabilityDate, StartTime, EndTime
      FROM EmployeeAvailability
     WHERE EmployeeUserID = :eid
       AND AvailabilityDate >= CURDATE()
     ORDER BY AvailabilityDate, StartTime
");
$avQ->execute([':eid' => $employeeID]);
$avails = $avQ->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>WrapLab Employee Dashboard</title>
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
    <h3 class="text-purple mb-4">Employee Dashboard</h3>
    <ul class="nav flex-column">
      <li class="nav-item mb-2"><a class="nav-link text-white" href="#my-profile">My Profile</a></li>
      <li class="nav-item mb-2"><a class="nav-link text-white" href="#my-tasks">My Tasks</a></li>
      <li class="nav-item mb-2"><a class="nav-link text-white" href="#availability">Set Availability</a></li>
      <li class="nav-item mb-2"><a class="nav-link text-white" href="#history">Appointment History</a></li>
      <li class="nav-item mt-4"><a class="nav-link text-purple" href="login.php">Log out</a></li>
    </ul>
  </nav>

  <!-- MAIN CONTENT -->
  <div class="flex-grow-1">
    <div class="container-fluid p-4">
      <div class="row">

        <!-- PROFILE & CHANGE PASSWORD -->
        <div class="col-lg-3 mb-5">
          <section id="my-profile">
            <h3 class="mb-3">My Profile</h3>
            <div class="card mb-4">
              <div class="card-body">
                <p><strong>Name:</strong> <?= htmlspecialchars("{$employee['FirstName']} {$employee['LastName']}") ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($employee['Email']) ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($employee['PhoneNumber']) ?></p>
                <p><strong>Job Title:</strong> <?= htmlspecialchars($employee['JobTitle']) ?></p>
                <p><strong>Specialization:</strong> <?= htmlspecialchars($employee['Specialization']) ?></p>
                <p><strong>Address:</strong> <?= htmlspecialchars($employee['Address']) ?></p>
              </div>
            </div>

            <h4 class="mb-3">Edit Personal Info</h4>
            <form action="employee.php" method="POST" class="mb-4">
            <input type="hidden" name="update_info" value="1">
              <div class="mb-2">
                <label for="phone" class="form-label">Phone Number</label>
                <input
                  type="text"
                  id="phone"
                  name="phone"
                  class="form-control"
                  value="<?= htmlspecialchars($employee['PhoneNumber']) ?>"
                  required
                >
              </div>
              <div class="mb-2">
                <label for="address" class="form-label">Address</label>
                <input
                  type="text"
                  id="address"
                  name="address"
                  class="form-control"
                  value="<?= htmlspecialchars($employee['Address']) ?>"
                  required
                >
              </div>
              <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
            </form>


            <h4 class="mb-3">Change Password</h4>
            <form id="password-form" action="update_password_employee.php" method="POST" class="row g-3">
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

        <!-- TASKS, AVAILABILITY & HISTORY -->
        <div class="col-lg-9">

          <!-- MY TASKS -->
          <section id="my-tasks" class="mb-5">
            <h3 class="mb-4">My Tasks</h3>
            <?php if (empty($appointments)): ?>
              <p>No current tasks.</p>
            <?php else: ?>
              <?php foreach ($appointments as $appt): ?>
                <?php if ($appt['Status'] !== 'Completed'): ?>
                  <div class="card mb-3 p-3">
                    <strong>Customer:</strong>
                      <?= htmlspecialchars($appt['CustomerFirstName'].' '.$appt['CustomerLastName']) ?><br>
                    <strong>Email:</strong>
                      <?= htmlspecialchars($appt['CustomerEmail']) ?><br>
                    <strong>Phone:</strong>
                      <?= htmlspecialchars($appt['CustomerPhone']) ?><br>
                    <strong>Preferred Contact:</strong>
                      <?= htmlspecialchars($appt['CustomerPref']) ?><br>

                    <strong>Service:</strong>
                      <?= htmlspecialchars($appt['OfferingName']) ?><br>

                    <strong>Vehicle:</strong>
                      <?php if($appt['Make']): ?>
                        <?= htmlspecialchars("{$appt['Make']} {$appt['Model']} ({$appt['Year']})") ?>
                        <?php if($appt['VINNumber']): ?>
                          , VIN <?= htmlspecialchars($appt['VINNumber']) ?>
                        <?php endif; ?>
                      <?php else: ?>
                        N/A
                      <?php endif; ?>
                      <br>

                                              <?php
                          $start = strtotime($appt['StartDate']);
                          $end   = strtotime($appt['EndDate']);
                          $same  = date('Y-m-d', $start) === date('Y-m-d', $end);
                        ?>
                        <strong>Date & Time:</strong>
                        <?= date('Y-m-d H:i', $start) ?>
                         – 
                        <?= $same ? date('H:i', $end) : date('Y-m-d H:i', $end) ?><br>
                      <?php /* make sure there is NO stray quote or ?> here */ ?>
                      <form method="POST" action="update_status.php" class="mt-2">
                      <input type="hidden" name="start_date"   value="<?= $appt['StartDate'] ?>">
                      <input type="hidden" name="end_date"     value="<?= $appt['EndDate'] ?>">
                      <select name="new_status" class="form-select w-auto d-inline">
                        <option <?= $appt['Status']=='Scheduled'   ? 'selected':'' ?>>Scheduled</option>
                        <option <?= $appt['Status']=='In Progress' ? 'selected':'' ?>>In Progress</option>
                        <option <?= $appt['Status']=='Completed'   ? 'selected':'' ?>>Completed</option>
                      </select>
                      <button type="submit" class="btn btn-dark btn-sm">Update</button>
                    </form>
                  </div>
                <?php endif; ?>
              <?php endforeach; ?>
            <?php endif; ?>
          </section>

          <!-- AVAILABILITY -->
          <section id="availability" class="mb-5">
            <h3 class="mb-3">Set Your Daily Availability</h3>
            <form action="set_availability.php" method="POST">
              <div class="mb-3">
                <label for="availability_date" class="form-label">Date</label>
                <input 
                  type="date" 
                  id="availability_date" 
                  name="availability_date" 
                  class="form-control w-auto" 
                  required 
                  value="<?= date('Y-m-d') ?>"
                >
              </div>
              <p>Select 1‑hour blocks you can work:</p>
              <div class="row gx-3 gy-2">
                <?php for ($h = 8; $h <= 17; $h++): 
                  $s = sprintf('%02d:00', $h);
                  $e = sprintf('%02d:00', $h + 1);
                  $id = str_replace(':','',$s);
                ?>
                  <div class="col-6 col-md-4 col-lg-3">
                    <div class="form-check">
                      <input 
                        class="form-check-input" 
                        type="checkbox" 
                        name="time_slots[]" 
                        value="<?= $s ?>" 
                        id="slot-<?= $id ?>"
                      >
                      <label class="form-check-label" for="slot-<?= $id ?>">
                        <?= "$s – $e" ?>
                      </label>
                    </div>
                  </div>
                <?php endfor; ?>
              </div>
              <button type="submit" class="btn btn-primary mt-3">Save Availability</button>
            </form>
          </section>

          <!-- UPCOMING AVAILABILITIES -->
          <section class="mb-5">
            <h3>Your Upcoming Availabilities</h3>
            <?php if (empty($avails)): ?>
              <p>No availabilities set.</p>
            <?php else: ?>
              <?php foreach ($avails as $av): ?>
                <div class="alert alert-secondary d-flex justify-content-between align-items-center">
                  <div>
                    📅 <strong><?= htmlspecialchars($av['AvailabilityDate']) ?></strong> |
                    ⏰ <?= htmlspecialchars(substr($av['StartTime'],0,5)) ?>–<?= htmlspecialchars(substr($av['EndTime'],0,5)) ?>
                  </div>
                  <form method="POST" action="employee.php" onsubmit="return confirm('Remove this slot?')">
                    <input type="hidden" name="availability_id" value="<?= $av['AvailabilityID'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </section>

          <!-- APPOINTMENT HISTORY -->
          <section id="history">
            <h3 class="mb-4">Appointment History</h3>
            <?php foreach ($appointments as $appt): ?>
              <?php if ($appt['Status'] === 'Completed'): ?>
                <div class="card mb-3 p-3">
                  <strong>Customer:</strong> <?= htmlspecialchars($appt['CustomerFirstName'].' '.$appt['CustomerLastName']) ?><br>
                  <strong>Service:</strong> <?= htmlspecialchars($appt['OfferingName']) ?><br>
                  <?php
                    $start = strtotime($appt['StartDate']);
                    $end   = strtotime($appt['EndDate']);
                  ?>
                  <strong>Date and Time:</strong>
                  <?= date('Y‑m‑d H:i', $start) ?> – <?= date('H:i', $end) ?><br>
                  <span class="badge bg-success">Completed</span>
                </div>
              <?php endif; ?>
            <?php endforeach; ?>
          </section>

        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Password mismatch & length check 
    document.getElementById('password-form')?.addEventListener('submit', function(e) {
      const n = document.getElementById('new_pwd').value.trim();
      const c = document.getElementById('confirm_pwd').value.trim();
      if (!n || !c) {
        e.preventDefault();
        alert("Both password fields are required.❗");
      } else if (n.length < 6) {
        e.preventDefault();
        alert("Password must be at least 6 characters long.❗");
      } else if (n !== c) {
        e.preventDefault();
        alert("Passwords do not match.❗");
        document.getElementById('confirm_pwd').focus();
      }
    });
  </script>
</body>
</html>
