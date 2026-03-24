<?php include '../includes/header.php'; ?>

<?php
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo "<div class='alert alert-danger'>Access denied.</div>";
    include '../includes/footer.php';
    exit;
}

$name = $price = $duration_minutes = $description = "";
$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? "");
    $price = trim($_POST['price'] ?? "");
    $duration_minutes = trim($_POST['duration_minutes'] ?? "");
    $description = trim($_POST['description'] ?? "");

    if ($name === "") $errors[] = "Service name is required.";
    if ($price === "" || !is_numeric($price)) $errors[] = "Valid price is required.";
    if ($duration_minutes === "" || !is_numeric($duration_minutes)) $errors[] = "Valid duration is required.";

    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "
            INSERT INTO services (name, price, duration_minutes, description)
            VALUES (?, ?, ?, ?)
        ");
        $priceVal = (float)$price;
        $durationVal = (int)$duration_minutes;
        mysqli_stmt_bind_param($stmt, "sdis", $name, $priceVal, $durationVal, $description);

        if (mysqli_stmt_execute($stmt)) {
            $success = "Service added successfully.";
            $name = $price = $duration_minutes = $description = "";
        } else {
            $errors[] = "Database error while adding service.";
        }
    }
}
?>

<h2 class="mb-4">Add Service</h2>

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
    <label class="form-label">Service Name</label>
    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name) ?>">
  </div>

  <div class="mb-3">
    <label class="form-label">Price (€)</label>
    <input type="number" step="0.01" name="price" class="form-control" value="<?= htmlspecialchars($price) ?>">
  </div>

  <div class="mb-3">
    <label class="form-label">Duration (minutes)</label>
    <input type="number" name="duration_minutes" class="form-control" value="<?= htmlspecialchars($duration_minutes) ?>">
  </div>

  <div class="mb-3">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($description) ?></textarea>
  </div>

  <button type="submit" class="btn btn-success">Add Service</button>
  <a href="admin_services.php" class="btn btn-secondary ms-2">Back</a>
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
<br>
<br>


<?php include '../includes/footer.php'; ?>