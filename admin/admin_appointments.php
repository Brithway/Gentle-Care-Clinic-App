<?php include '../includes/header.php'; ?>

<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../PHPMailer/src/Exception.php';
require_once '../PHPMailer/src/PHPMailer.php';
require_once '../PHPMailer/src/SMTP.php';
require_once '../config/mail.php';
?>

<?php
// Must be logged in + admin
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

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appt_id'], $_POST['new_status'])) {
    $appt_id = (int)$_POST['appt_id'];
    $new_status = $_POST['new_status'];

    $allowed = ['Confirmed', 'Cancelled', 'Completed', 'Pending'];

    if (!in_array($new_status, $allowed, true)) {
        $errorMsg = "Invalid status.";
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE appointments SET status=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "si", $new_status, $appt_id);

        if (mysqli_stmt_execute($stmt)) {
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                $successMsg = "Appointment updated to {$new_status}.";
                // Fetch patient + appointment details for email
                $emailStmt = mysqli_prepare($conn, "
                    SELECT u.full_name, u.email,
                          d.name AS doctor_name,
                          s.name AS service_name,
                          a.appointment_date,
                          a.appointment_time
                    FROM appointments a
                    JOIN users u ON a.user_id = u.id
                    JOIN doctors d ON a.doctor_id = d.id
                    JOIN services s ON a.service_id = s.id
                    WHERE a.id = ?
                ");
                mysqli_stmt_bind_param($emailStmt, "i", $appt_id);
                mysqli_stmt_execute($emailStmt);
                $emailRes = mysqli_stmt_get_result($emailStmt);

                if ($emailRow = mysqli_fetch_assoc($emailRes)) {
                    $patientName = $emailRow['full_name'];
                    $patientEmail = $emailRow['email'];
                    $doctorName = $emailRow['doctor_name'];
                    $serviceName = $emailRow['service_name'];
                    $apptDate = $emailRow['appointment_date'];
                    $apptTime = substr($emailRow['appointment_time'], 0, 5);

                    $mail = new PHPMailer(true);

                    try {
                        $mail->isSMTP();
                        $mail->Host       = MAIL_HOST;
                        $mail->Port       = MAIL_PORT;
                        $mail->SMTPAuth   = true;
                        $mail->Username   = MAIL_USERNAME;
                        $mail->Password   = MAIL_PASSWORD;

                        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
                        $mail->addAddress($patientEmail, $patientName);

                        $mail->isHTML(true);
                        $mail->Subject = "Gentle Care Clinic | Appointment Status Updated";
                        $mail->Body = "
                            <h2>Appointment Status Updated</h2>
                            <p>Dear {$patientName},</p>
                            <p>Your appointment status has been updated.</p>
                            <hr>
                            <p><strong>Doctor:</strong> {$doctorName}</p>
                            <p><strong>Service:</strong> {$serviceName}</p>
                            <p><strong>Date:</strong> {$apptDate}</p>
                            <p><strong>Time:</strong> {$apptTime}</p>
                            <p><strong>New Status:</strong> {$new_status}</p>
                            <hr>
                            <p>Thank you,<br>Gentle Care Clinic</p>
                        ";

                        $mail->send();
                    } catch (Exception $e) {
                        // Ignore email failure so admin update still works
                    }
                }
            } else {
                $errorMsg = "No changes made.";
            }
        } else {
            $errorMsg = "Database error while updating status.";
        }
    }
}

// Handle DELETE appointment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int)$_POST['delete_id'];

    $stmt = mysqli_prepare($conn, "DELETE FROM appointments WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $deleteId);

    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            $successMsg = "Appointment deleted successfully.";
        } else {
            $errorMsg = "Appointment not found.";
        }
    } else {
        $errorMsg = "Error deleting appointment.";
    }
}

// FILTER (GET)
$selectedStatus = $_GET['status'] ?? '';
$statuses = ['Pending', 'Confirmed', 'Cancelled', 'Completed'];

$where = "";
if ($selectedStatus !== "" && in_array($selectedStatus, $statuses, true)) {
    $where = "WHERE a.status = ?";
} else {
    $selectedStatus = "";
}

// Fetch appointments
$sql = "
    SELECT a.id, a.appointment_date, a.appointment_time, a.status, a.created_at,
           u.full_name, u.email,
           d.name AS doctor_name, d.specialty,
           s.name AS service_name, s.price, s.duration_minutes
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    JOIN doctors d ON a.doctor_id = d.id
    JOIN services s ON a.service_id = s.id
    $where
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
";

$stmt = mysqli_prepare($conn, $sql);
if ($where !== "") {
    mysqli_stmt_bind_param($stmt, "s", $selectedStatus);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<div class="page-hero">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h1 class="fw-bold mb-1">Admin: Appointments</h1>
      <div class="text-muted">Manage bookings and update appointment status.</div>
    </div>
    <a href="../index.php" class="btn btn-outline-success">Back to Home</a>
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

  <form method="GET" class="mb-3 d-flex gap-2 align-items-center">
    <label class="fw-semibold mb-0">Filter by status:</label>

    <select name="status" class="form-select w-auto" onchange="this.form.submit()">
      <option value="" <?= $selectedStatus === '' ? 'selected' : '' ?>>All</option>
      <?php foreach ($statuses as $s): ?>
        <option value="<?= htmlspecialchars($s) ?>" <?= $selectedStatus === $s ? 'selected' : '' ?>>
          <?= htmlspecialchars($s) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </form>

  <?php if (!$result || mysqli_num_rows($result) === 0): ?>
    <div class="alert alert-info mb-0">No appointments found.</div>
  <?php else: ?>

    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Patient</th>
            <th>Doctor</th>
            <th>Service</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>

        <tbody>
        <?php $sno = 1; ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td><?= $sno++ ?></td>

            <td>
              <div class="fw-semibold"><?= htmlspecialchars($row['full_name']) ?></div>
              <div class="text-muted small"><?= htmlspecialchars($row['email']) ?></div>
            </td>

            <td>
              <div class="fw-semibold"><?= htmlspecialchars($row['doctor_name']) ?></div>
              <div class="text-muted small"><?= htmlspecialchars($row['specialty']) ?></div>
            </td>

            <td>
              <div class="fw-semibold"><?= htmlspecialchars($row['service_name']) ?></div>
              <div class="text-muted small">
                €<?= number_format((float)$row['price'], 2) ?> • <?= (int)$row['duration_minutes'] ?> mins
              </div>
            </td>

            <td><?= htmlspecialchars($row['appointment_date']) ?></td>
            <td><?= htmlspecialchars(substr($row['appointment_time'], 0, 5)) ?></td>

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
              <a href="edit_appointments.php?id=<?= (int)$row['id'] ?>" class="btn btn-sm btn-warning">
                Edit
              </a>

              <form method="POST" class="d-inline">
                <input type="hidden" name="delete_id" value="<?= (int)$row['id'] ?>">
                <button type="submit"
                        class="btn btn-sm btn-danger"
                        onclick="return confirm('Delete this appointment?')">
                  Delete
                </button>
              </form>

              <form method="POST" class="d-inline-flex gap-2 mt-1">
                <input type="hidden" name="appt_id" value="<?= (int)$row['id'] ?>">

                <select name="new_status" class="form-select form-select-sm" required>
                  <option value="">Status</option>
                  <?php foreach ($statuses as $s): ?>
                    <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
                  <?php endforeach; ?>
                </select>

                <button type="submit" class="btn btn-sm btn-success">
                  Update
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