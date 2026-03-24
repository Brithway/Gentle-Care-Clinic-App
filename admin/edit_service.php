<?php include '../includes/header.php'; ?>

<?php
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo "<div class='alert alert-danger'>Access denied.</div>";
    include '../includes/footer.php';
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: admin_services.php");
    exit;
}

$id = (int)$_GET['id'];

$stmt = mysqli_prepare($conn, "SELECT * FROM services WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($res) !== 1) {
    echo "<div class='alert alert-danger'>Service not found.</div>";
    include '../includes/footer.php';
    exit;
}

$service = mysqli_fetch_assoc($res);

$success = "";
$errors = [];

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
            UPDATE services
            SET name=?, price=?, duration_minutes=?, description=?
            WHERE id=?
        ");
        $priceVal = (float)$price;
        $durationVal = (int)$duration_minutes;
        mysqli_stmt_bind_param($stmt, "sdisi", $name, $priceVal, $durationVal, $description, $id);

        if (mysqli_stmt_execute($stmt)) {
            $success = "Service updated successfully.";

            $stmt = mysqli_prepare($conn, "SELECT * FROM services WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $service = mysqli_fetch_assoc($res);
        } else {
            $errors[] = "Database error while updating service.";
        }
    }
}
?>

<h2 class="mb-4">Edit Service</h2>

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
    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($service['name']) ?>">
  </div>

  <div class="mb-3">
    <label class="form-label">Price (€)</label>
    <input type="number" step="0.01" name="price" class="form-control" value="<?= htmlspecialchars($service['price']) ?>">
  </div>

  <div class="mb-3">
    <label class="form-label">Duration (minutes)</label>
    <input type="number" name="duration_minutes" class="form-control" value="<?= htmlspecialchars($service['duration_minutes']) ?>">
  </div>

  <div class="mb-3">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($service['description']) ?></textarea>
  </div>

  <button type="submit" class="btn btn-success">Update Service</button>
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