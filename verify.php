<?php

session_start();
include 'config/database.php';
if (!isset($_SESSION['verify_userid'])) {
    header('Location: login.php');
    exit;
}
$userid = $_SESSION['verify_userid'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedIC = trim($_POST['icnumber']);
    $newPassword = trim($_POST['newpassword']);
    $confirmPassword = trim($_POST['cpassword']);
    if (empty($submittedIC) || empty($newPassword) || empty($confirmPassword)) {
        echo "<script>alert('Please fill in all fields.'); window.history.back();</script>";
        exit;
    }
    $ic_check_stmt = $conn->prepare("SELECT employeeIC FROM employee WHERE employeeID = ?");
    $ic_check_stmt->bind_param("s", $userid);
    $ic_check_stmt->execute();
    $result = $ic_check_stmt->get_result();
    if ($result->num_rows === 1) {
        $employee = $result->fetch_assoc();
        $db_ic = $employee['employeeIC'];
        if ($submittedIC !== $db_ic) {
            echo "<script>alert('The IC Number provided does not match our records. Please try again or contact the administrator.'); window.history.back();</script>";
            exit;
        }
    } else {
        echo "<script>alert('An error occurred. Could not find user record.'); window.location.href='login.php';</script>";
        exit;
    }
    $ic_check_stmt->close();

    // --- NEW: PASSWORD LENGTH VALIDATION ---
    // Check if the new password is at least 8 characters long.
    if (strlen($newPassword) < 8) {
        // If it's too short, show an error and stop.
        echo "<script>alert('Password must be at least 8 characters long.'); window.history.back();</script>";
        exit;
    }

    if ($newPassword !== $confirmPassword) {
        echo "<script>alert('Passwords do not match.'); window.history.back();</script>";
        exit;
    }
    $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE userID = ?");
    $updateStmt->bind_param("ss", $newHashedPassword, $userid);
    if ($updateStmt->execute()) {
        unset($_SESSION['verify_userid']);
        echo "<script>alert('Verification successful. Please login with your new password.'); window.location.href='login.php';</script>";
    } else {
        echo "<script>alert('Could not update password. Please try again.'); window.history.back();</script>";
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Account Verification - GFM+</title>

  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700;800&display=swap" rel="stylesheet">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  
  ->
  <link rel="stylesheet" href="css/login-style.css">
</head>
<body>
  <div class="page-wrapper container">
    <!-- 1. Floating Navigation Bar -->
    <header id="header">
      <nav class="navbar navbar-expand-lg navbar-light bg-white">
        <div class="container-fluid">
          <a class="navbar-brand" href="index.html">
            <img src="assets/download-removebg-preview.png" alt="GFM Logo" style="height: 27px;">
          </a>
          <a href="login.php" class="btn btn-outline-primary rounded-pill d-none d-lg-block">
            <i class="fas fa-arrow-left me-2"></i>Back to Login
          </a>
        </div>
      </nav>
    </header>

    <!-- Main Content Area -->
    <main class="container">
      <div class="row align-items-center" style="min-height: 75vh;">
        <!-- Left Column: Instructions & Branding -->
        <div class="col-lg-7 d-none d-lg-block">
          <h1 class="display-4 fw-bold text-white">Secure Your Account</h1>
          <p class="lead text-white mt-3">
            <span style="font-weight: 600;">Verify your identity and create a personal password.</span>
          </p>
          <div class="mt-4 p-4 rounded-4" style="background-color: rgba(0,0,0,0.1);">
              <h5 class="text-white">You are verifying account:</h5>
              <p class="display-6 text-white fw-bold mb-0">
                <?php echo htmlspecialchars($userid); ?>
              </p>
          </div>
        </div>

        <!-- Right Column: Verification Form Card -->
        <div class="col-lg-5">
          <div class="card login-card p-4 p-sm-5 shadow">
            <h3 class="text-center mb-1">Create Your Password</h3>
            <p class="text-center text-muted mb-4">Please confirm your IC to proceed.</p>

            <form action="verify.php" method="POST">
              <div class="form-floating mb-3">
                <input type="text" class="form-control" id="icnumber" name="icnumber" placeholder="xxxxxx-xx-xxxx" required>
                <label for="icnumber">IC Number (xxxxxx-xx-xxxx)</label>
              </div>
              <div class="form-floating mb-3">
                <input type="password" class="form-control" id="newpassword" name="newpassword" placeholder="At least 8 characters" required>
                <label for="newpassword">New Password</label>
              </div>
              <div class="form-floating mb-3">
                <input type="password" class="form-control" id="cpassword" name="cpassword" placeholder="Confirm new password" required>
                <label for="cpassword">Confirm Password</label>
              </div>
              <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary btn-lg">Set Password & Verify</button>
              </div>
            </form>

            <div class="text-center mt-4 border-top pt-3">
              <small class="text-muted">Having trouble? Contact Admin</small>
              <br>
              <small class="text-muted text-center">011-16467016</small>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>