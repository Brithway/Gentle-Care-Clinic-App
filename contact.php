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
$name = $email = $subject = $message = "";
$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $subject = trim($_POST['subject'] ?? "");
    $message = trim($_POST['message'] ?? "");

    if ($name === "") $errors[] = "Name is required.";
    if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if ($subject === "") $errors[] = "Subject is required.";
    if ($message === "") $errors[] = "Message is required.";

    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "
            INSERT INTO contact_messages (name, email, subject, message)
            VALUES (?, ?, ?, ?)
        ");
        mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $subject, $message);

        if (mysqli_stmt_execute($stmt)) {
            $success = "Your message has been sent successfully.";

            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = MAIL_HOST;
                $mail->Port       = MAIL_PORT;
                $mail->SMTPAuth   = true;
                $mail->Username   = MAIL_USERNAME;
                $mail->Password   = MAIL_PASSWORD;

                $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
                $mail->addAddress('support@gentlecareclinic.com', 'Gentle Care Clinic');

                $mail->isHTML(true);
                $mail->Subject = "New Contact Form Message";
                $mail->Body = "
                    <h2>New Contact Form Submission</h2>
                    <p><strong>Name:</strong> {$name}</p>
                    <p><strong>Email:</strong> {$email}</p>
                    <p><strong>Subject:</strong> {$subject}</p>
                    <p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>
                ";

                $mail->send();
            } catch (Exception $e) {
                // Ignore email failure so form still works
            }

            $name = $email = $subject = $message = "";
        } else {
            $errors[] = "Database error while sending message.";
        }
    }
}
?>

<div class="page-hero mb-4">
  <h1 class="fw-bold mb-2">Contact Us</h1>
  <p class="text-muted mb-0">
    Have a question about appointments, clinic services, or opening hours? Contact Gentle Care Clinic below.
  </p>
</div>

<div class="row g-4">
  <div class="col-lg-7">
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
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name) ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">Subject</label>
        <input type="text" name="subject" class="form-control" value="<?= htmlspecialchars($subject) ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">Message</label>
        <textarea name="message" class="form-control" rows="5"><?= htmlspecialchars($message) ?></textarea>
      </div>

      <button type="submit" class="btn btn-success">Send Message</button>
    </form>
  </div>

  <div class="col-lg-5">
    <div class="gc-card bg-white p-4 h-100">
      <h4 class="fw-bold mb-3">Clinic Information</h4>
      <p class="mb-2"><strong>Phone:</strong> +353 1 234 5678</p>
      <p class="mb-2"><strong>Email:</strong> info@gentlecare.ie</p>
      <p class="mb-3"><strong>Address:</strong> 24 Main Street, Dublin, Ireland</p>

      <h5 class="fw-semibold mt-4">Opening Hours</h5>
      <ul class="list-unstyled text-muted">
        <li>Monday – Friday: 9:00 AM – 5:00 PM</li>
        <li>Saturday: 10:00 AM – 2:00 PM</li>
        <li>Sunday: Closed</li>
      </ul>

      <h5 class="fw-semibold mt-4">Why Contact Us?</h5>
      <p class="text-muted mb-0">
        Patients can use this form to ask about appointments, available services,
        doctor schedules, or general clinic support.
      </p>
    </div>
  </div>
</div>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<?php include 'includes/footer.php'; ?>