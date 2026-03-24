<?php include 'includes/header.php'; ?>

<section class="page-hero mb-5">
  <div class="row align-items-center g-4">
    <div class="col-lg-7">
      <h1 class="fw-bold mb-3">Welcome to Gentle Care Clinic</h1>
      <p class="lead text-muted mb-4">
        Gentle Care Clinic is a modern healthcare platform designed to make booking medical
        appointments simple, secure, and user-friendly for every patient.
      </p>
      <div class="d-flex gap-2 flex-wrap">
        <a href="book.php" class="btn btn-success btn-lg">Book an Appointment</a>
        <a href="about.php" class="btn btn-outline-success btn-lg">Learn More</a>
      </div>
    </div>

    <div class="col-lg-5">
      <img src="assets/images/clinic-home-hero.jpg"
           alt="Clinic reception"
           class="img-fluid rounded shadow"
           style="width:100%; height:100%; object-fit:cover; min-height:320px;">
    </div>
  </div>
</section>

<div class="row g-4 mb-5">
  <div class="col-md-4">
    <div class="gc-card bg-white p-4 h-100">
      <h4 class="fw-bold mb-3">Patient-Friendly Booking</h4>
      <p class="text-muted mb-0">
        Patients can register, log in, and book appointments online with the doctor and service
        that best suits their needs.
      </p>
    </div>
  </div>

  <div class="col-md-4">
    <div class="gc-card bg-white p-4 h-100">
      <h4 class="fw-bold mb-3">Trusted Medical Team</h4>
      <p class="text-muted mb-0">
        Gentle Care Clinic offers access to qualified doctors from multiple specialties,
        ensuring reliable care and professional consultation.
      </p>
    </div>
  </div>

  <div class="col-md-4">
    <div class="gc-card bg-white p-4 h-100">
      <h4 class="fw-bold mb-3">Efficient Management</h4>
      <p class="text-muted mb-0">
        The clinic system supports both patient access and administrator controls for
        managing appointments and improving workflow.
      </p>
    </div>
  </div>
</div>

<div class="row g-4 mb-5">
  <div class="col-lg-6">
    <img src="assets/images/clinic-home-support.jpg"
         alt="Doctor and patient"
         class="img-fluid rounded shadow"
         style="width:100%; height:100%; object-fit:cover; min-height:280px;">
  </div>

  <div class="col-lg-6">
    <div class="gc-card bg-white p-4 h-100">
      <h3 class="fw-bold mb-3">Our Commitment</h3>
      <p class="text-muted">
        We are committed to providing accessible and organised healthcare services through a
        digital platform that helps patients save time and manage appointments effectively.
      </p>
      <p class="text-muted mb-0">
        This web application demonstrates practical clinic scheduling, user authentication,
        appointment management, and admin-based monitoring in one integrated system.
      </p>
    </div>
  </div>
</div>

<div class="gc-card bg-white p-4">
  <h3 class="fw-bold mb-3">Why Patients Use Our Website</h3>
  <div class="row g-3">
    <div class="col-md-6">
      <ul class="text-muted mb-0">
        <li>Quick online account registration</li>
        <li>Secure login and appointment access</li>
        <li>Doctor and service selection</li>
      </ul>
    </div>
    <div class="col-md-6">
      <ul class="text-muted mb-0">
        <li>Appointment history and status tracking</li>
        <li>Clean and professional interface</li>
        <li>Role-based admin controls behind the scenes</li>
      </ul>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>