<?php include 'includes/header.php'; ?>

<?php
$full_name = $email = "";
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name = trim($_POST['full_name'] ?? "");
    $email     = trim($_POST['email'] ?? "");
    $password  = $_POST['password'] ?? "";
    $confirm   = $_POST['confirm_password'] ?? "";

    // Validation
    if ($full_name === "") $errors[] = "Full name is required.";
    if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if ($password === "") $errors[] = "Password is required.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
    if ($password !== $confirm) $errors[] = "Passwords do not match.";

    if (empty($errors)) {
        // Check email already exists
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($res) > 0) {
            $errors[] = "This email is already registered. Please login.";
        } else {
            // Insert new user
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt2 = mysqli_prepare($conn, "INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, 'patient')");
            mysqli_stmt_bind_param($stmt2, "sss", $full_name, $email, $hash);

            if (mysqli_stmt_execute($stmt2)) {
                header("Location: login.php?registered=1");
                exit;
            } else {
                $errors[] = "Database error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<div class="page-hero">
  <h1 class="fw-bold mb-2">Create Account</h1>
  <p class="text-muted mb-4">Register to book and manage your appointments.</p>

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
      <input type="text" name="full_name" class="form-control"
             value="<?= htmlspecialchars($full_name) ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Email Address</label>
      <input type="email" name="email" class="form-control"
             value="<?= htmlspecialchars($email) ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" required>
      <div class="form-text">Minimum 6 characters.</div>
    </div>

    <div class="mb-3">
      <label class="form-label">Confirm Password</label>
      <input type="password" name="confirm_password" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-success">Register</button>
    <a href="login.php" class="btn btn-outline-success ms-2">Already have an account?</a>
  </form>
</div>

<?php include 'includes/footer.php'; ?>