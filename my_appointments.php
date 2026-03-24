<?php include 'includes/header.php'; ?>

<?php
// Must be logged in
if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-warning'>
            Please <a href='login.php'>login</a> to view your appointments.
          </div>";
    include 'includes/footer.php';
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$successMsg = "";
$errorMsg = "";

// Handle Cancel Appointment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    $cancelId = (int)$_POST['cancel_id'];

    // Only cancel if this appointment belongs to this user
    $stmt = mysqli_prepare($conn, "
        UPDATE appointments
        SET status='Cancelled'
        WHERE id=? AND user_id=? AND status IN ('Pending','Confirmed')
    ");
    mysqli_stmt_bind_param($stmt, "ii", $cancelId, $user_id);

    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            $successMsg = "Appointment cancelled successfully.";
        } else {
            $errorMsg = "Unable to cancel (it may already be cancelled/completed).";
        }
    } else {
        $errorMsg = "Database error while cancelling.";
    }
}

// Fetch appointments for this user
$stmt2 = mysqli_prepare($conn, "
    SELECT a.id, a.appointment_date, a.appointment_time, a.status, a.created_at,
           d.name AS doctor_name, d.specialty,
           s.name AS service_name, s.price, s.duration_minutes
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.id
    JOIN services s ON a.service_id = s.id
    WHERE a.user_id = ? AND a.status != 'Cancelled'
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
mysqli_stmt_bind_param($stmt2, "i", $user_id);
mysqli_stmt_execute($stmt2);
$appointments = mysqli_stmt_get_result($stmt2);
?>

<div class="page-hero">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h1 class="fw-bold mb-1">My Appointments</h1>
      <div class="text-muted">View, track, and cancel your bookings.</div>
    </div>
    <a href="book.php" class="btn btn-success">Book New</a>
  </div>
</div>

<?php if ($successMsg): ?>
  <div class="alert alert-success mt-3"><?= htmlspecialchars($successMsg) ?></div>
<?php endif; ?>

<?php if ($errorMsg): ?>
  <div class="alert alert-danger mt-3"><?= htmlspecialchars($errorMsg) ?></div>
<?php endif; ?>

<div class="gc-card bg-white p-3 mt-3">
  <?php if (mysqli_num_rows($appointments) === 0): ?>
    <div class="alert alert-info mb-0">
      You have no appointments yet. <a href="book.php" class="alert-link">Book one now</a>.
    </div>
  <?php else: ?>

    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Doctor</th>
            <th>Service</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
            <th class="text-end">Action</th>
          </tr>
        </thead>
        <tbody>
        <?php $sno = 1; ?>
        <?php while ($row = mysqli_fetch_assoc($appointments)): ?>
          <tr>
            <td><?= $sno++ ?></td>
            <td>
              <div class="fw-semibold"><?= htmlspecialchars($row['doctor_name']) ?></div>
              <div class="text-muted small"><?= htmlspecialchars($row['specialty']) ?></div>
            </td>
            <td>
              <div class="fw-semibold"><?= htmlspecialchars($row['service_name']) ?></div>
              <div class="text-muted small">
                €<?= number_format($row['price'],2) ?> • <?= (int)$row['duration_minutes'] ?> mins
              </div>
            </td>
            <td><?= htmlspecialchars($row['appointment_date']) ?></td>
            <td><?= htmlspecialchars(substr($row['appointment_time'],0,5)) ?></td>
            <td>
              <?php
                $status = $row['status'];
                $badge = "secondary";
                if ($status === "Pending") $badge = "warning";
                if ($status === "Confirmed") $badge = "success";
                if ($status === "Cancelled") $badge = "danger";
                if ($status === "Completed") $badge = "dark";
              ?>
              <span class="badge text-bg-<?= $badge ?>"><?= htmlspecialchars($status) ?></span>
            </td>
            <td class="text-end">
              <?php if ($status === "Pending" || $status === "Confirmed"): ?>
                <form method="POST" class="d-inline">
                  <input type="hidden" name="cancel_id" value="<?= (int)$row['id'] ?>">
                  <button type="submit"
                          class="btn btn-sm btn-outline-danger"
                          onclick="return confirm('Cancel this appointment?');">
                    Cancel
                  </button>
                </form>
              <?php else: ?>
                <span class="text-muted small">—</span>
              <?php endif; ?>
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
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>


<?php include 'includes/footer.php'; ?>