<?php
require "includes/db_connect.php";

// Fetch colleges
$colleges = $conn->query("SELECT * FROM colleges ORDER BY college_name");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Signup - WMSTAY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="bg-light">
<div class="container d-flex justify-content-center align-items-center min-vh-100">
  <div class="card shadow p-4" style="max-width: 500px; width: 100%;">
    <div class="card-body">
      <h2 class="card-title text-center mb-4">Student Registration</h2>
      <form action="signup.php" method="POST" id="signupForm">
        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" name="full_name" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">School Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Confirm Password</label>
          <input type="password" name="confirm_password" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">College</label>
          <select name="college_id" id="collegeSelect" class="form-control" required>
            <option value="">Select College</option>
            <?php while($college = $colleges->fetch_assoc()): ?>
              <option value="<?= $college['id'] ?>"><?= htmlspecialchars($college['college_name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Program</label>
          <select name="program_id" id="programSelect" class="form-control" required disabled>
            <option value="">Select a college first</option>
          </select>
        </div>
        <div class="form-group">
          <label>Gender</label>
          <select name="gender" required>
            <option value="">Select gender</option>
            <option>Male</option>
            <option>Female</option>
            <option>Prefer not to say</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Contact Number</label>
          <input type="text" name="contact" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Address</label>
          <textarea name="address" class="form-control" required></textarea>
        </div>

        <button class="btn btn-primary w-100" type="submit" name="signup">Create Account</button>
      </form>

      <p class="text-center mt-3">Already have an account? <a href="login.php">Login here</a></p>
    </div>
  </div>
</div>

<script>
document.getElementById('collegeSelect').addEventListener('change', function() {
    const collegeId = this.value;
    const programSelect = document.getElementById('programSelect');
    programSelect.innerHTML = '<option value="">Loading...</option>';
    programSelect.disabled = true;

    if (collegeId) {
          fetch('/wmstay/get_programs.php?college_id=' + collegeId)
            .then(response => response.json())
            .then(data => {
                programSelect.innerHTML = '<option value="">Select Program</option>';
                data.forEach(program => {
                    const option = document.createElement('option');
                    option.value = program.id;
                    option.textContent = program.program_name;
                    programSelect.appendChild(option);
                });
                programSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error:', error);
                programSelect.innerHTML = '<option value="">Error loading programs</option>';
            });
    } else {
        programSelect.innerHTML = '<option value="">Select a college first</option>';
    }
});

document.getElementById('signupForm').addEventListener('submit', function(e) {
    const college = document.getElementById('collegeSelect').value;
    const program = document.getElementById('programSelect').value;
    if (college && !program) {
        e.preventDefault();
        alert('Please select a program.');
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php
// Backend in same file for simplicity:
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    require __DIR__ . '/includes/db_connect.php';

    $full      = trim($_POST['full_name']);
    $email     = strtolower(trim($_POST['email']));
    $pass      = $_POST['password'];
    $confirm   = $_POST['confirm_password'];
    $college_id= (int)$_POST['college_id'];
    $program_id= (int)$_POST['program_id'];
    $gender    = $_POST['gender'];
    $contact   = $_POST['contact'];
    $address   = $_POST['address'];

    if ($pass !== $confirm) {
        echo "<script>alert('Passwords do not match');window.location='signup.php';</script>";
        exit;
    }

    // Validate college and program
    $college = $conn->query("SELECT college_name FROM colleges WHERE id=$college_id")->fetch_assoc();
    $program = $conn->query("SELECT program_name FROM programs WHERE id=$program_id AND college_id=$college_id")->fetch_assoc();
    if (!$college || !$program) {
        echo "<script>alert('Invalid college or program selection');window.location='signup.php';</script>";
        exit;
    }

    // Generate student_number from email EH202201447@wmsu.edu.ph -> 2022-01447
    $emailUpper = strtoupper($email);
    if (preg_match('/^[A-Z]{2}([0-9]{4})([0-9]{5})/', $emailUpper, $m)) {
        $yearCode = $m[1];
        $seq      = $m[2];
        $student_number = $yearCode . '-' . $seq;
    } else {
        $student_number = 'WMSU' . rand(1000000, 9999999);
    }

    $hashed = password_hash($pass, PASSWORD_BCRYPT);

    // Split full name
    $parts = explode(' ', $full, 2);
    $full_name = $full; // store whole as one

    $stmt = $conn->prepare(
        "INSERT INTO students (student_number, email, password_hash, full_name, department, program, gender, contact, address, college_id, program_id)
         VALUES (?,?,?,?,?,?,?,?,?,?,?)"
    );
    $stmt->bind_param(
        'sssssssssii',
        $student_number, $email, $hashed, $full_name,
        $college['college_name'], $program['program_name'], $gender, $contact, $address, $college_id, $program_id
    );

    if ($stmt->execute()) {
        echo "<script>alert('Account created! Your Student ID is $student_number');window.location='login.php';</script>";
    } else {
        echo "<script>alert('Registration failed (maybe email already exists)');window.location='signup.php';</script>";
    }
}
?>
</body>
</html>