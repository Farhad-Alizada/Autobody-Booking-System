<?php
session_start();
require_once 'db_connect.php';

// Redirect if not logged in as Customer
if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Customer') {
    header('Location: login.html');
    exit();
}

// Fetch service offerings
$stmt = $pdo->query("SELECT OfferingID, OfferingName FROM ServiceOffering");
$offerings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>WrapLab Customer Dashboard</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="styles.css" />
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
        <!-- SECTION: Service Booking -->
        <section id="book-service" class="mb-5">
            <h3 class="mb-4">Book a Service</h3>
            <form action="book_service.php" method="POST">
                <!-- Service Selection -->
                <div class="mb-3">
                    <label for="serviceSelect" class="form-label">Select Service</label>
                    <select id="serviceSelect" name="service_id" class="form-select" required>
                        <option value="">Choose a service...</option>
                        <?php foreach ($offerings as $offering): ?>
                            <option value="<?= htmlspecialchars($offering['OfferingID']) ?>">
                                <?= htmlspecialchars($offering['OfferingName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Vehicle Information -->
                <div class="mb-3">
                    <label for="vehicleMake" class="form-label">Vehicle Make</label>
                    <input type="text" id="vehicleMake" name="vehicle_make" class="form-control" placeholder="e.g., Toyota" required>
                </div>

                <div class="mb-3">
                    <label for="vehicleModel" class="form-label">Vehicle Model</label>
                    <input type="text" id="vehicleModel" name="vehicle_model" class="form-control" placeholder="e.g., Corolla" required>
                </div>

                <div class="mb-3">
                    <label for="vehicleYear" class="form-label">Vehicle Year</label>
                    <input type="number" id="vehicleYear" name="vehicle_year" class="form-control" placeholder="e.g., 2021" required>
                </div>

                <div class="mb-3">
                    <label for="vinNumber" class="form-label">VIN Number (Optional)</label>
                    <input type="text" id="vinNumber" name="vin_number" class="form-control" placeholder="e.g., 1HGCM82633A123456">
                </div>

                <!-- Date & Time -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="serviceDate" class="form-label">Select Date</label>
                        <input type="date" id="serviceDate" name="service_date" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label for="serviceTime" class="form-label">Select Time</label>
                        <input type="time" id="serviceTime" name="service_time" class="form-control" required>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary">Book Now</button>
            </form>
        </section>

        <!-- SECTION: My Appointments -->
        <section id="my-appointments" class="mb-5">
            <h3 class="mb-4">My Appointments</h3>
            <div class="row" id="appointments-container">
                <!-- Appointments will be dynamically loaded -->
            </div>
        </section>

        <!-- SECTION: Leave Feedback -->
        <section id="feedback" class="mb-5">
            <h3 class="mb-4">Leave Feedback</h3>
            <form action="submit_feedback.php" method="POST">
                <div class="mb-3">
                    <label for="feedbackName" class="form-label">Your Name</label>
                    <input type="text" id="feedbackName" name="feedbackName" class="form-control" placeholder="Enter your name..." required />
                </div>
                <div class="mb-3">
                    <label for="feedbackText" class="form-label">Your Feedback</label>
                    <textarea id="feedbackText" name="feedback" rows="3" class="form-control" placeholder="Write your feedback here..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit Feedback</button>
            </form>
        </section>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Alerts Script -->
<script>
    const params = new URLSearchParams(window.location.search);
    if (params.get("message") === "booking_success") {
        alert("✅ Booking successful!");
    } else if (params.get("error") === "invalid_input") {
        alert("⚠️ Please fill out all fields.");
    } else if (params.get("error") === "duplicate_booking") {
        alert("⚠️ You’ve already booked this service at that time.");
    }
</script>

<!-- Load Appointments Dynamically -->
<script>
    window.addEventListener('DOMContentLoaded', () => {
        fetch('appointments.php')
            .then(response => response.text())
            .then(data => {
                document.getElementById('appointments-container').innerHTML = data;
            })
            .catch(error => {
                console.error("Failed to load appointments:", error);
                document.getElementById('appointments-container').innerHTML = "<p class='text-danger'>Failed to load appointments.</p>";
            });
    });
</script>
</body>
</html>
