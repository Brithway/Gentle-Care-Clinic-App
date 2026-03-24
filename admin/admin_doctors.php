<?php include '../includes/header.php'; ?>

<?php
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo "<div class='alert alert-danger'>
            Access denied. Admins only.
            <a href='../index.php' class='alert-link'>Back to Home</a>
          </div>";
    include '../includes/footer.php';
    exit;
}

$successMsg = "";
$errorMsg = "";

// Delete doctor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int)$_POST['delete_id'];

    $stmt = mysqli_prepare($conn, "DELETE FROM doctors WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $deleteId);

    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            $successMsg = "Doctor deleted successfully.";
        } else {
            $errorMsg = "Doctor not found.";
        }
    } else {
        $errorMsg = "Cannot delete this doctor. It may be linked to appointments.";
    }
}

$result = mysqli_query($conn, "SELECT * FROM doctors ORDER BY name");
?>

<div class="page-hero">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h1 class="fw-bold mb-1">Admin: Doctors</h1>
      <div class="text-muted">Add, edit, and remove doctors.</div>
    </div>
    <div class="d-flex gap-2">
      <a href="add_doctor.php" class="btn btn-success">Add Doctor</a>
      <a href="../index.php" class="btn btn-outline-success">Back to Home</a>
    </div>
  </div>
</div>

<div class="d-flex gap-2 mt-3 mb-3">
  <a href="admin_appointments.php" class="btn btn-outline-success">Manage Appointments</a>
  <a href="admin_doctors.php" class="btn btn-outline-success">Manage Doctors</a>
  <a href="admin_services.php" class="btn btn-outline-success">Manage Services</a>
</div>

<?php if ($successMsg): ?>
  <div class="alert alert-success mt-3"><?= htmlspecialchars($successMsg) ?></div>
<?php endif; ?>

<?php if ($errorMsg): ?>
  <div class="alert alert-danger mt-3"><?= htmlspecialchars($errorMsg) ?></div>
<?php endif; ?>

<div class="gc-card bg-white p-3 mt-3">
  <?php if (!$result || mysqli_num_rows($result) === 0): ?>
    <div class="alert alert-info mb-0">No doctors found.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Specialty</th>
            <th>Photo</th>
            <th>Bio</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php $sno = 1; ?>
        <?php while ($doctor = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td><?= $sno++ ?></td>
            <td><?= htmlspecialchars($doctor['name']) ?></td>
            <td><?= htmlspecialchars($doctor['specialty']) ?></td>
            <td><?= htmlspecialchars($doctor['photo']) ?></td>
            <td><?= htmlspecialchars($doctor['bio']) ?></td>
            <td class="text-end">
              <a href="edit_doctor.php?id=<?= (int)$doctor['id'] ?>" class="btn btn-sm btn-warning">
                Edit
              </a>

              <form method="POST" class="d-inline">
                <input type="hidden" name="delete_id" value="<?= (int)$doctor['id'] ?>">
                <button type="submit"
                        class="btn btn-sm btn-danger"
                        onclick="return confirm('Delete this doctor?')">
                  Delete
                </button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

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

<?php include '../includes/footer.php'; ?>