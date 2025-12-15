<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WMSTAY Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="bg-light">
<div class="container d-flex justify-content-center align-items-center min-vh-100">
  <div class="card shadow p-4" style="max-width: 400px; width: 100%;">
    <div class="card-body">
      <h2 class="card-title text-center mb-4">Login</h2>
      <form action="/wmstay/includes/auth.php" method="POST">
        <div class="mb-3">
          <label class="form-label">School Email / Student No. / Admin Username</label>
          <input type="text" name="login_id" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100" type="submit" name="login">Login</button>
      </form>
      <p class="text-center mt-3">
        Student? <a href="signup.php">Create a new account</a>
      </p>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>