<?php include 'includes/header.php'; ?>

<?php
$result = mysqli_query($conn, "SELECT * FROM doctors ORDER BY name");
?>

<div class="page-hero mb-4">
  <h1 class="fw-bold mb-2">Our Doctors</h1>
  <p class="text-muted mb-0">
    Meet the experienced healthcare professionals available at Gentle Care Clinic.
  </p>
</div>

<?php if (!$result || mysqli_num_rows($result) === 0): ?>
  <div class="alert alert-info">No doctors found.</div>
<?php else: ?>
  <div class="row g-4">
    <?php while ($doctor = mysqli_fetch_assoc($result)): ?>
      <div class="col-md-6 col-lg-4">
        <div class="gc-card bg-white h-100 p-3">

          <?php if (!empty($doctor['photo'])): ?>
            <img src="assets/images/<?= htmlspecialchars($doctor['photo']) ?>"
                 alt="<?= htmlspecialchars($doctor['name']) ?>"
                 class="img-fluid rounded mb-3"
                 style="height: 220px; width: 100%; object-fit: cover;">
          <?php else: ?>
            <div class="bg-light border rounded d-flex align-items-center justify-content-center mb-3"
                 style="height: 220px;">
              <span class="text-muted">No Photo Available</span>
            </div>
          <?php endif; ?>

          <h4 class="fw-bold mb-1"><?= htmlspecialchars($doctor['name']) ?></h4>
          <p class="text-success fw-semibold mb-2"><?= htmlspecialchars($doctor['specialty']) ?></p>

          <p class="text-muted mb-0">
            <?= nl2br(htmlspecialchars($doctor['bio'])) ?>
          </p>

        </div>
      </div>
    <?php endwhile; ?>
  </div>
<?php endif; ?>
<br>
<br>
<br>
<br>
<br>



<?php include 'includes/footer.php'; ?>