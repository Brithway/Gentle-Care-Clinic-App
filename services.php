<?php include 'includes/header.php'; ?>

<?php
$result = mysqli_query($conn, "SELECT * FROM services ORDER BY name");
?>

<div class="page-hero mb-4">
  <h1 class="fw-bold mb-2">Our Services</h1>
  <p class="text-muted mb-0">
    Explore the healthcare services offered at Gentle Care Clinic.
  </p>
</div>

<?php if (!$result || mysqli_num_rows($result) === 0): ?>
  <div class="alert alert-info">No services found.</div>
<?php else: ?>
  <div class="row g-4">
    <?php while ($service = mysqli_fetch_assoc($result)): ?>
      <div class="col-md-6 col-lg-4">
        <div class="gc-card bg-white h-100 p-3">
          <h4 class="fw-bold mb-2"><?= htmlspecialchars($service['name']) ?></h4>
          <p class="text-success fw-semibold mb-2">€<?= number_format((float)$service['price'], 2) ?></p>
          <p class="text-muted mb-2">Duration: <?= (int)$service['duration_minutes'] ?> minutes</p>
          <p class="text-muted mb-0">
            <?= nl2br(htmlspecialchars($service['description'])) ?>
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
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>





<?php include 'includes/footer.php'; ?>