<?php include '../includes/header.php'; ?>

<?php
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo "<div class='alert alert-danger'>Access denied.</div>";
    include '../includes/footer.php';
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: admin_doctors.php");
    exit;
}

$id = (int)$_GET['id'];

$stmt = mysqli_prepare($conn, "SELECT * FROM doctors WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($res) !== 1) {
    echo "<div class='alert alert-danger'>Doctor not found.</div>";
    include '../includes/footer.php';
    exit;
}

$doctor = mysqli_fetch_assoc($res);

// Fetch all services
$servicesResult = mysqli_query($conn, "SELECT * FROM services ORDER BY name");

// Fetch currently assigned services
$currentServices = [];
$currentMap = mysqli_query($conn, "SELECT service_id FROM doctor_services WHERE doctor_id = $id");
while ($row = mysqli_fetch_assoc($currentMap)) {
    $currentServices[] = $row['service_id'];
}

$success = "";
$errors = [];

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
            UPDATE doctors
            SET name=?, specialty=?, photo=?, bio=?
            WHERE id=?
        ");
        mysqli_stmt_bind_param($stmt, "ssssi", $name, $specialty, $photo, $bio, $id);

        if (mysqli_stmt_execute($stmt)) {
            // Replace doctor_services mappings
            mysqli_query($conn, "DELETE FROM doctor_services WHERE doctor_id = $id");

            foreach ($selectedServices as $serviceId) {
                $serviceId = (int)$serviceId;
                $mapStmt = mysqli_prepare($conn, "
                    INSERT INTO doctor_services (doctor_id, service_id)
                    VALUES (?, ?)
                ");
                mysqli_stmt_bind_param($mapStmt, "ii", $id, $serviceId);
                mysqli_stmt_execute($mapStmt);
            }

            $success = "Doctor updated successfully.";

            // Refresh doctor
            $stmt = mysqli_prepare($conn, "SELECT * FROM doctors WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $doctor = mysqli_fetch_assoc($res);

            // Refresh service list
            $currentServices = $selectedServices;
        } else {
            $errors[] = "Database error while updating doctor.";
        }
    }
}
?>

<h2 class="mb-4">Edit Doctor</h2>

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
    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($doctor['name']) ?>">
  </div>

  <div class="mb-3">
    <label class="form-label">Specialty</label>
    <input type="text" name="specialty" class="form-control" value="<?= htmlspecialchars($doctor['specialty']) ?>">
  </div>

  <div class="mb-3">
    <label class="form-label">Photo Filename</label>
    <input type="text" name="photo" class="form-control" value="<?= htmlspecialchars($doctor['photo']) ?>">
  </div>

  <div class="mb-3">
    <label class="form-label">Bio</label>
    <textarea name="bio" class="form-control" rows="4"><?= htmlspecialchars($doctor['bio']) ?></textarea>
  </div>

  <div class="mb-3">
    <label class="form-label">Services Offered</label>
    <?php mysqli_data_seek($servicesResult, 0); ?>
    <?php while ($service = mysqli_fetch_assoc($servicesResult)): ?>
      <div class="form-check">
        <input class="form-check-input"
               type="checkbox"
               name="services[]"
               value="<?= (int)$service['id'] ?>"
               id="service<?= (int)$service['id'] ?>"
               <?= in_array($service['id'], $currentServices) ? 'checked' : '' ?>>
        <label class="form-check-label" for="service<?= (int)$service['id'] ?>">
          <?= htmlspecialchars($service['name']) ?> (€<?= number_format((float)$service['price'], 2) ?>)
        </label>
      </div>
    <?php endwhile; ?>
  </div>

  <button type="submit" class="btn btn-success">Update Doctor</button>
  <a href="admin_doctors.php" class="btn btn-secondary ms-2">Back</a>
</form>

<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>


<?php include '../includes/footer.php'; ?>