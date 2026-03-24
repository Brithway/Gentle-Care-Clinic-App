<?php include 'includes/header.php'; ?>

<?php
$email = "";
$errors = [];

if (isset($_SESSION['user_id'])) {
    header("Location: book.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";

    if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if ($password === "") $errors[] = "Password is required.";

    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "SELECT id, full_name, email, password_hash, role FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($res) !== 1) {
            $errors[] = "Invalid email or password.";
        } else {
            $user = mysqli_fetch_assoc($res);

            if (!password_verify($password, $user['password_hash'])) {
                $errors[] = "Invalid email or password.";
            } else {
                // Login success
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role']      = $user['role'];

                header("Location: book.php?login=1");
                exit;
            }
        }
    }
}
?>

<div class="page-hero">
  <h1 class="fw-bold mb-2">Login</h1>
  <p class="text-muted mb-4">Access your account to book appointments.</p>

  <?php if (isset($_GET['registered'])): ?>
    <div class="alert alert-success">Registration successful. Please login.</div>
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
      <label class="form-label">Email Address</label>
      <input type="email" name="email" class="form-control"
             value="<?= htmlspecialchars($email) ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-success">Login</button>
    <a href="register.php" class="btn btn-outline-success ms-2">Create Account</a>
  </form>
</div>

<?php include 'includes/footer.php'; ?>