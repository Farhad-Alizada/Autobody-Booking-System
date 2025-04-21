<?php
  require_once 'db_connect.php';

  // grab the 5 most recent feedback entries
  $stmt = $pdo->query("
    SELECT f.Comments, f.Rating, f.FeedbackDate, f.FeedbackName,
           u.FirstName, u.LastName
      FROM feedback f
 LEFT JOIN users u ON f.CustomerUserID = u.UserID
  ORDER BY f.FeedbackDate DESC
  LIMIT 5
  ");
  $feedbackRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>WrapLab Auto Styling</title>
  <!-- Bootstrap CSS -->
  <link 
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
  />
  <!-- Bootstrap Icons -->
  <link 
    rel="stylesheet" 
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"
  />
  <!-- Google Fonts -->
  <link 
    href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" 
    rel="stylesheet"
  >
  <!-- Custom CSS -->
  <link rel="stylesheet" href="styles.css" />
</head>
<body>

  <!-- LOGO -->
  <header class="text-center py-4">
    <img src="pictures/logo.png" alt="WrapLab Logo" style="height: 80px;" />
  </header>

  <!-- NAVIGATION -->
  <nav class="border-top border-bottom py-2">
    <ul class="nav justify-content-center">
      <li class="nav-item">
        <a class="nav-link text-dark" href="#hero">HOME</a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-dark" href="#services">SERVICES</a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-dark" href="#feedback">FEEDBACK</a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-dark" href="#about">ABOUT US</a>
      </li>
      <li class="nav-item">
        <!-- Book With Us -->
        <a class="nav-link text-dark" href="login.html">BOOK WITH US</a>
      </li>
      <li class="nav-item">
        <!-- LOG IN -->
        <a class="nav-link text-purple fw-bold" href="login.html">LOG IN</a>
      </li>
    </ul>
  </nav>

  <!-- HERO -->
  <section id="hero" class="py-5 bg-black">
    <div class="container text-center text-white">
      <h2 class="fw-bold text-purple">Welcome to WrapLab</h2>
      <p class="mx-auto" style="max-width: 700px;">
        We pride ourselves on offering quality, speed, and value for money on all our car services.
      </p>
      <p class="mx-auto" style="max-width: 500px;">
        With over 2 years of experience within the industry, whether you simply want your windows tinted,
        are looking for a hood wrap, or a full commercial van wrap, our trusted team of specialists can
        help you find the perfect solution. We are based in Calgary, Alberta and work in all surrounding areas.
      </p>
    </div>
  </section>

  <!-- WHY WRAPLAB? -->
  <section id="why-wraplab" class="container py-5 text-center">
    <h2 class="mb-5 fw-bold">
      Why <span class="text-purple">WrapLab</span>?
    </h2>
    <div class="row gy-4 gx-5 justify-content-center">
      <div class="col-sm-6 col-md-4 col-lg">
        <img src="pictures/whywraplab1.gif" alt="Expand Limits" class="img-fluid mb-3" style="max-height:100px">
        <p class="fw-semibold">Expand Limits in Vehicle Enhancement</p>
      </div>
      <div class="col-sm-6 col-md-4 col-lg">
        <img src="pictures/whywraplab2.gif" alt="Custom Solutions" class="img-fluid mb-3" style="max-height:100px">
        <p class="fw-semibold">Custom Solutions for Your Vehicle</p>
      </div>
      <div class="col-sm-6 col-md-4 col-lg">
        <img src="pictures/whywraplab3.gif" alt="Quality & Precision" class="img-fluid mb-3" style="max-height:100px">
        <p class="fw-semibold">Dedicated to Quality and Precision</p>
      </div>
      <div class="col-sm-6 col-md-4 col-lg">
        <img src="pictures/whywraplab4.gif" alt="Trusted" class="img-fluid mb-3" style="max-height:100px">
        <p class="fw-semibold">Trusted by Car Enthusiasts</p>
      </div>
      <div class="col-sm-6 col-md-4 col-lg">
        <img src="pictures/whywraplab5.gif" alt="Innovators" class="img-fluid mb-3" style="max-height:100px">
        <p class="fw-semibold">Innovators in Vehicle Services</p>
      </div>
    </div>
  </section>

  <!-- OUR SERVICES -->
  <section id="services" class="py-5 bg-black">
    <div class="container">
      <h2 class="mb-4 text-white">Our <span class="text-purple">Services</span></h2>
  
      <!--  Main.js will inject cards here  -->
      <div class="row text-center" id="services-container">
        <div class="text-white-50">Loading…</div>
      </div>
    </div>
  </section>

  <!-- TESTIMONIALS -->
<section id="feedback" class="py-5 bg-light">
  <div class="container">
    <h2 class="text-center mb-4">What Our Customers Are Saying</h2>
    <div class="row">
      <?php if (empty($feedbackRows)): ?>
        <div class="col-12 text-center">
          No feedback yet—be the first to review us!
        </div>
      <?php else: ?>
        <?php foreach ($feedbackRows as $fb): ?>
          <div class="col-md-4 mb-4">
            <div class="card h-100">
              <div class="card-body">
                <p class="card-text">
                  “<?= htmlspecialchars($fb['Comments']) ?>”
                </p>
              </div>
              <div class="card-footer">
                <small class="text-muted">
                  — <?= htmlspecialchars($fb['FeedbackName'] ?: $fb['FirstName']) ?>,
                    <?= date('M j, Y', strtotime($fb['FeedbackDate'])) ?>
                </small>
                <div>
                  <?php for ($i = 0; $i < $fb['Rating']; $i++): ?>
                    ⭐
                  <?php endfor; ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

  <!-- ABOUT US -->
  <section id="about" class="py-5 bg-black">
    <div class="container text-white">
      <div class="row align-items-center">
        <div class="col-md-4 text-center mb-4 mb-md-0">
          <img src="pictures/wraplablogo2.png" alt="WrapLab Original Automotive Styling" class="img-fluid" style="max-height:250px">
        </div>
        <div class="col-md-8">
          <h2 class="text-purple">Who We Are</h2>
          <p>Wraplab is a car and painting automotive styling company located in Calgary, Alberta. Started in early 2024, we bring a new and enthusiastic approach to auto styling—one that is set to change the car scene in Calgary. Already in a few years, we’ve been able to pursue many projects and work on a variety of vehicles with different clients.</p>
          <p>Our goal is not just to be a service, but a service you can remember. Even though Wraplab was established not so long ago, we have been working on cars with passion for more than 4 years. With a combined experience of 14 years, we can assure you that our work is professional and our work ethic is second to none.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="text-center p-5 bg-white">
    <h1 class="fw-bold mb-3" style="font-size: 3rem;">Let Us Work With You!</h1>
    <p class="mb-4" style="font-size: 1.3rem;">Quality and <span class="text-purple fw-bold">professional</span> work is our promise</p>
    <div class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-4 mb-4" style="font-size:1rem;">
      <div><i class="bi bi-envelope-fill me-1"></i>info@wraplabcalgary.com</div>
      <div><i class="bi bi-geo-alt-fill me-1"></i>NW Calgary</div>
      <div><i class="bi bi-telephone-fill me-1"></i>403.918.3110</div>
    </div>
    <p class="mb-0 text-muted">© 2025 WrapLab Auto Styling</p>
  </footer>

   <!-- Bootstrap JS bundle -->
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

   <!-- 👇  run the dynamic service loader -->
   <script src="main.js"></script>
 </body>
 </html>