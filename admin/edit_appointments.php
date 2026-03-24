<?php include '../includes/header.php'; ?>

<?php
// Admin check
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo "<div class='alert alert-danger'>
            Access denied.
            <a href='../index.php'>Back</a>
          </div>";
    include '../includes/footer.php';
    exit;
}

// Check ID
if (!isset($_GET['id'])) {
    header("Location: admin_appointments.php");
    exit;
}

$id = (int)$_GET['id'];

// Fetch appointment
$stmt = mysqli_prepare($conn, "SELECT * FROM appointments WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($res) !== 1) {
    echo "<div class='alert alert-danger'>Appointment not found.</div>";
    include '../includes/footer.php';
    exit;
}

$data = mysqli_fetch_assoc($res);

// Fetch doctors + services
$doctors = mysqli_query($conn, "SELECT * FROM doctors ORDER BY name");
$services = mysqli_query($conn, "SELECT * FROM services ORDER BY name");

$success = "";
$error = "";

// Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = (int)$_POST['doctor_id'];
    $service_id = (int)$_POST['service_id'];
    $date = $_POST['appointment_date'];
    $time = $_POST['appointment_time'];

    $stmt = mysqli_prepare($conn, "
        UPDATE appointments
        SET doctor_id=?, service_id=?, appointment_date=?, appointment_time=?
        WHERE id=?
    ");
    mysqli_stmt_bind_param($stmt, "iissi", $doctor_id, $service_id, $date, $time, $id);

    if (mysqli_stmt_execute($stmt)) {
        $success = "Appointment updated successfully.";

        // Refresh data after update
        $stmt = mysqli_prepare($conn, "SELECT * FROM appointments WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($res);
    } else {
        $error = "Error updating appointment.";
    }
}
?>

<h2 class="mb-4">Edit Appointment</h2>

<?php if ($success): ?>
  <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($error): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" class="gc-card bg-white p-4">

  <div class="mb-3">
    <label class="form-label">Doctor</label>
    <select name="doctor_id" class="form-select">
      <?php while ($d = mysqli_fetch_assoc($doctors)): ?>
        <option value="<?= $d['id'] ?>" <?= ($data['doctor_id'] == $d['id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($d['name']) ?>
        </option>
      <?php endwhile; ?>
    </select>
  </div>

  <div class="mb-3">
    <label class="form-label">Service</label>
    <select name="service_id" class="form-select">
      <?php while ($s = mysqli_fetch_assoc($services)): ?>
        <option value="<?= $s['id'] ?>" <?= ($data['service_id'] == $s['id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($s['name']) ?>
        </option>
      <?php endwhile; ?>
    </select>
  </div>

  <div class="mb-3">
    <label class="form-label">Date</label>
    <input type="date" name="appointment_date" class="form-control"
           value="<?= htmlspecialchars($data['appointment_date']) ?>">
  </div>

  <div class="mb-3">
    <label class="form-label">Time</label>
    <input type="time" name="appointment_time" class="form-control"
           value="<?= htmlspecialchars(substr($data['appointment_time'], 0, 5)) ?>">
  </div>

  <button type="submit" class="btn btn-success">Update</button>
  <a href="admin_appointments.php" class="btn btn-secondary ms-2">Back</a>
</form>

<?php include '../includes/footer.php'; ?>