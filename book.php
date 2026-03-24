<?php include 'includes/header.php'; ?>

<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'config/mail.php';
?>

<?php
// Must be logged in
if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-warning'>
            Please <a href='login.php'>login</a> to book an appointment.
          </div>";
    include 'includes/footer.php';
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Fetch doctors
$doctors = mysqli_query($conn, "SELECT id, name, specialty FROM doctors ORDER BY name");

// Fetch doctor-service mapping
$doctorServices = [];
$mapQuery = mysqli_query($conn, "
    SELECT ds.doctor_id, s.id AS service_id, s.name, s.price, s.duration_minutes
    FROM doctor_services ds
    JOIN services s ON ds.service_id = s.id
    ORDER BY s.name
");

while ($row = mysqli_fetch_assoc($mapQuery)) {
    $doctorServices[$row['doctor_id']][] = [
        'id' => $row['service_id'],
        'name' => $row['name'],
        'price' => $row['price'],
        'duration_minutes' => $row['duration_minutes']
    ];
}

// Time slots (30 min)
$timeSlots = [
  "09:00:00","09:30:00","10:00:00","10:30:00","11:00:00","11:30:00",
  "12:00:00","12:30:00","13:00:00","13:30:00","14:00:00","14:30:00",
  "15:00:00","15:30:00","16:00:00","16:30:00"
];

$errors = [];
$success = false;

$doctor_id = $service_id = "";
$appointment_date = $appointment_time = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $doctor_id = $_POST['doctor_id'] ?? "";
    $service_id = $_POST['service_id'] ?? "";
    $appointment_date = $_POST['appointment_date'] ?? "";
    $appointment_time = $_POST['appointment_time'] ?? "";

    // Validation
    if (!is_numeric($doctor_id)) $errors[] = "Please select a doctor.";
    if (!is_numeric($service_id)) $errors[] = "Please select a service.";
    if ($appointment_date === "") $errors[] = "Please select a date.";
    if ($appointment_time === "") $errors[] = "Please select a time slot.";

    if ($appointment_date !== "" && strtotime($appointment_date) < strtotime(date("Y-m-d"))) {
        $errors[] = "Appointment date cannot be in the past.";
    }

    if ($appointment_time !== "" && !in_array($appointment_time, $timeSlots, true)) {
        $errors[] = "Invalid time slot selected.";
    }

    // Check that selected service actually belongs to selected doctor
    if (empty($errors)) {
        $doctor_id = (int)$doctor_id;
        $service_id = (int)$service_id;

        $checkStmt = mysqli_prepare($conn, "
            SELECT id FROM doctor_services
            WHERE doctor_id = ? AND service_id = ?
        ");
        mysqli_stmt_bind_param($checkStmt, "ii", $doctor_id, $service_id);
        mysqli_stmt_execute($checkStmt);
        $checkRes = mysqli_stmt_get_result($checkStmt);

        if (mysqli_num_rows($checkRes) === 0) {
            $errors[] = "Selected service is not available for this doctor.";
        }
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "
            INSERT INTO appointments (user_id, doctor_id, service_id, appointment_date, appointment_time, status)
            VALUES (?, ?, ?, ?, ?, 'Pending')
        ");
        mysqli_stmt_bind_param($stmt, "iiiss", $user_id, $doctor_id, $service_id, $appointment_date, $appointment_time);

        if (mysqli_stmt_execute($stmt)) {
          $success = true;

          // Get doctor name
          $doctorName = "";
          $docStmt = mysqli_prepare($conn, "SELECT name FROM doctors WHERE id = ?");
          mysqli_stmt_bind_param($docStmt, "i", $doctor_id);
          mysqli_stmt_execute($docStmt);
          $docRes = mysqli_stmt_get_result($docStmt);
          if ($docRow = mysqli_fetch_assoc($docRes)) {
              $doctorName = $docRow['name'];
          }

          // Get service name
          $serviceName = "";
          $srvStmt = mysqli_prepare($conn, "SELECT name FROM services WHERE id = ?");
          mysqli_stmt_bind_param($srvStmt, "i", $service_id);
          mysqli_stmt_execute($srvStmt);
          $srvRes = mysqli_stmt_get_result($srvStmt);
          if ($srvRow = mysqli_fetch_assoc($srvRes)) {
              $serviceName = $srvRow['name'];
          }

          // Get logged-in user details
          $userStmt = mysqli_prepare($conn, "SELECT full_name, email FROM users WHERE id = ?");
          mysqli_stmt_bind_param($userStmt, "i", $user_id);
          mysqli_stmt_execute($userStmt);
          $userRes = mysqli_stmt_get_result($userStmt);
          $userData = mysqli_fetch_assoc($userRes);

          $patientName = $userData['full_name'] ?? 'Patient';
          $patientEmail = $userData['email'] ?? '';

          if ($patientEmail !== '') {
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
                  $mail->Subject = "Gentle Care Clinic | Appointment Confirmation";
                  $mail->Body = "
                      <h2>Appointment Booked Successfully</h2>
                      <p>Dear {$patientName},</p>
                      <p>Your appointment has been booked successfully.</p>
                      <hr>
                      <p><strong>Doctor:</strong> {$doctorName}</p>
                      <p><strong>Service:</strong> {$serviceName}</p>
                      <p><strong>Date:</strong> {$appointment_date}</p>
                      <p><strong>Time:</strong> " . substr($appointment_time, 0, 5) . "</p>
                      <p><strong>Status:</strong> Pending</p>
                      <hr>
                      <p>Thank you,<br>Gentle Care Clinic</p>
                  ";

                  $mail->send();
              } catch (Exception $e) {
                  // Ignore email failure so booking still works
              }
          }

          $doctor_id = $service_id = $appointment_date = $appointment_time = "";
          } else {
            $errors[] = "That doctor/time slot is already booked. Please choose another time.";
        }
    }
}
?>

<div class="page-hero">
  <h1 class="fw-bold mb-2">Book an Appointment</h1>
  <p class="text-muted mb-4">Choose a doctor, then select one of their available services.</p>

  <?php if (isset($_GET['login'])): ?>
    <div class="alert alert-success">Login successful. You can now book an appointment.</div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="alert alert-success">
      Appointment booked successfully (Status: Pending).
      <a href="my_appointments.php" class="alert-link">View My Appointments</a>
    </div>
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
      <label class="form-label">Doctor</label>
      <select name="doctor_id" id="doctorSelect" class="form-select" required>
        <option value="">-- Select Doctor --</option>
        <?php mysqli_data_seek($doctors, 0); ?>
        <?php while ($d = mysqli_fetch_assoc($doctors)): ?>
          <option value="<?= $d['id'] ?>" <?= ($doctor_id == $d['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($d['name']) ?> (<?= htmlspecialchars($d['specialty']) ?>)
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Service</label>
      <select name="service_id" id="serviceSelect" class="form-select" required disabled>
        <option value="">-- Select Doctor First --</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Date</label>
      <input type="date" name="appointment_date" class="form-control"
             value="<?= htmlspecialchars($appointment_date) ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Time Slot</label>
      <select name="appointment_time" class="form-select" required>
        <option value="">-- Select Time --</option>
        <?php foreach ($timeSlots as $t): ?>
          <option value="<?= $t ?>" <?= ($appointment_time == $t) ? 'selected' : '' ?>>
            <?= substr($t, 0, 5) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <button type="submit" class="btn btn-success btn-lg">Book Appointment</button>
    <a href="my_appointments.php" class="btn btn-outline-success btn-lg ms-2">My Appointments</a>
  </form>
</div>

<script>
const doctorServices = <?= json_encode($doctorServices) ?>;
const doctorSelect = document.getElementById('doctorSelect');
const serviceSelect = document.getElementById('serviceSelect');

function populateServices(selectedDoctorId, selectedServiceId = "") {
    serviceSelect.innerHTML = "";

    if (!selectedDoctorId || !doctorServices[selectedDoctorId]) {
        serviceSelect.disabled = true;
        serviceSelect.innerHTML = '<option value="">-- Select Doctor First --</option>';
        return;
    }

    serviceSelect.disabled = false;
    serviceSelect.innerHTML = '<option value="">-- Select Service --</option>';

    doctorServices[selectedDoctorId].forEach(service => {
        const option = document.createElement('option');
        option.value = service.id;
        option.textContent = `${service.name} (€${parseFloat(service.price).toFixed(2)}, ${service.duration_minutes} mins)`;

        if (selectedServiceId && selectedServiceId == service.id) {
            option.selected = true;
        }

        serviceSelect.appendChild(option);
    });
}

doctorSelect.addEventListener('change', function() {
    populateServices(this.value);
});

// Keep selected service on validation errors
window.addEventListener('DOMContentLoaded', function() {
    populateServices("<?= htmlspecialchars((string)$doctor_id) ?>", "<?= htmlspecialchars((string)$service_id) ?>");
});
</script>

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