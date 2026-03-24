<?php include '../includes/header.php'; ?>

<?php
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo "<div class='alert alert-danger'>Access denied.</div>";
    include '../includes/footer.php';
    exit;
}

$name = $specialty = $photo = $bio = "";
$errors = [];
$success = "";

// Fetch all services for checkboxes
$servicesResult = mysqli_query($conn, "SELECT * FROM services ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? "");
    $specialty = trim($_POST['specialty'] ?? "");
    $photo = trim($_POST['photo'] ?? "");
    $bio = trim($_POST['bio'] ?? "");
    $selectedServices = $_POST['services'] ?? [];

    if ($name === "") $errors[] = "Doctor name is required.";
    if ($specialty === "") $errors[] = "Specialty is required.";
    if (empty($selectedServices)) $errors[] = "Please assign at least one service.";

    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "
            INSERT INTO doctors (name, specialty, photo, bio)
            VALUES (?, ?, ?, ?)
        ");
        mysqli_stmt_bind_param($stmt, "ssss", $name, $specialty, $photo, $bio);

        if (mysqli_stmt_execute($stmt)) {
            $doctorId = mysqli_insert_id($conn);

            foreach ($selectedServices as $serviceId) {
                $serviceId = (int)$serviceId;
                $mapStmt = mysqli_prepare($conn, "
                    INSERT INTO doctor_services (doctor_id, service_id)
                    VALUES (?, ?)
                ");
                mysqli_stmt_bind_param($mapStmt, "ii", $doctorId, $serviceId);
                mysqli_stmt_execute($mapStmt);
            }

            $success = "Doctor added successfully.";
            $name = $specialty = $photo = $bio = "";
        } else {
            $errors[] = "Database error while adding doctor.";
        }
    }
}
?>

<h2 class="mb-4">Add Doctor</h2>

<?php if ($success): ?>
  <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <?php foreach ($errors as $e): ?>
      <div><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<form method="POST" class="gc-card bg-white p-4">
  <div class="mb-3">
    <label class="form-label">Doctor Name</label>
    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name) ?>">
  </div>

  <div class="mb-3">
    <label class="form-label">Specialty</label>
    <input type="text" name="specialty" class="form-control" value="<?= htmlspecialchars($specialty) ?>">
  </div>

  <div class="mb-3">
    <label class="form-label">Photo Filename</label>
    <input type="text" name="photo" class="form-control" value="<?= htmlspecialchars($photo) ?>" placeholder="doctor4.jpg">
  </div>

  <div class="mb-3">
    <label class="form-label">Bio</label>
    <textarea name="bio" class="form-control" rows="4"><?= htmlspecialchars($bio) ?></textarea>
  </div>

  <div class="mb-3">
    <label class="form-label">Services Offered</label>
    <?php mysqli_data_seek($servicesResult, 0); ?>
    <?php while ($service = mysqli_fetch_assoc($servicesResult)): ?>
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="services[]" value="<?= (int)$service['id'] ?>" id="service<?= (int)$service['id'] ?>">
        <label class="form-check-label" for="service<?= (int)$service['id'] ?>">
          <?= htmlspecialchars($service['name']) ?> (€<?= number_format((float)$service['price'], 2) ?>)
        </label>
      </div>
    <?php endwhile; ?>
  </div>

  <button type="submit" class="btn btn-success">Add Doctor</button>
  <a href="admin_doctors.php" class="btn btn-secondary ms-2">Back</a>
</form>

<br>
<br>
<br>
<br>
<br>
<br>
<br>



<?php include '../includes/footer.php'; ?>