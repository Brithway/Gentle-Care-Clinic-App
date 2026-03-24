<?php
session_start();
require_once __DIR__ . '/../config/connect.php';

$base = "/gentlecare/";
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gentle Care Clinic</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= $base ?>assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-gc">
  <div class="container">
    <a class="navbar-brand fw-bold d-flex align-items-center" href="<?= $base ?>index.php">
      <span class="brand-badge">GC</span>
      Gentle Care Clinic
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">

        <li class="nav-item"><a class="nav-link" href="<?= $base ?>index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= $base ?>about.php">About</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= $base ?>doctors.php">Doctors</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= $base ?>services.php">Services</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= $base ?>contact.php">Contact</a></li>

        <?php if (isset($_SESSION['user_id'])): ?>
          <li class="nav-item"><a class="nav-link" href="<?= $base ?>book.php">Book</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= $base ?>my_appointments.php">My Appointments</a></li>

          <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
            <li class="nav-item">
              <a class="nav-link fw-semibold text-warning" href="<?= $base ?>admin/admin_appointments.php">Admin</a>
            </li>
          <?php endif; ?>

          <li class="nav-item">
            <span class="nav-link disabled">Hi, <?= htmlspecialchars($_SESSION['full_name'] ?? 'User') ?></span>
          </li>
          <li class="nav-item">
            <a class="btn btn-sm btn-light ms-lg-2" href="<?= $base ?>logout.php">Logout</a>
          </li>

        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?= $base ?>register.php">Register</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= $base ?>login.php">Login</a></li>
        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>

<main class="container py-4">