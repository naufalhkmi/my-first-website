<?php

session_start();
include 'config/database.php';
$loginError = "";
$employees_by_team = [];
$employees_query = "SELECT employeeID, employeeFullName, employeeEmail, team.teamName FROM employee LEFT JOIN team ON employee.teamID = team.teamID ORDER BY team.teamName, employee.employeeFullName ASC";
$employees_result = $conn->query($employees_query);
if ($employees_result) {
    while ($row = $employees_result->fetch_assoc()) {
        $team_name = !empty($row['teamName']) ? $row['teamName'] : 'Unassigned';
        $employees_by_team[$team_name][] = $row;
    }
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (empty(trim($_POST["userid"]))) {
        $loginError = "Please enter User ID.";
    } else {
        $userid = trim($_POST["userid"]);
        $password = trim($_POST["password"]);
        $stmt = $conn->prepare("SELECT users.userID, users.password, users.category, employee.roles, employee.teamID FROM users LEFT JOIN employee ON users.userID = employee.employeeID WHERE users.userID = ?");
        $stmt->bind_param("s", $userid);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $db_hash = $user["password"];
            if (password_verify('newuser', $db_hash)) {
                $_SESSION['verify_userid'] = $user['userID'];
                echo "<script>alert('New user detected. Please create your password to complete verification.'); window.location.href = 'verify.php';</script>";
                exit;
            }
            if (password_verify($password, $db_hash)) {
                session_regenerate_id(true);
                $_SESSION["employeeID"] = $user["userID"];
                $_SESSION["category"] = $user["category"];
                $_SESSION["teamID"] = $user["teamID"];
                $_SESSION['role'] = $user['roles'];
                if ($user["category"] === "admin") {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: employee/employee-dashboard.php");
                }
                exit;
            } else {
                $loginError = "Incorrect User ID or Password.";
            }
        } else {
            $loginError = "Incorrect User ID or Password.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>GFM+ Login</title>
  
  <!-- Google Fonts (Poppins) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700;800&display=swap" rel="stylesheet">

  <!-- Bootstrap & Font Awesome -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

  <!-- Custom Login CSS -->
  <link rel="stylesheet" href="css/login-style.css">
</head>
<body>

  <div class="page-wrapper container">
    <!-- 1. Floating Navigation Bar (Matches landing page) -->
    <header id="header">
      <nav class="navbar navbar-expand-lg navbar-light bg-white">
        <div class="container-fluid">
          <a class="navbar-brand" href="index.html">
            <img src="assets/download-removebg-preview.png" alt="GFM Logo" style="height: 27px;">
          </a>
          <a href="index.php" class="btn btn-outline-primary rounded-pill d-none d-lg-block">
            <i class="fas fa-arrow-left me-2"></i>Back to Home
          </a>
        </div>
      </nav>
    </header>

    <!-- Main Content Area -->
    <main class="container">
        <div class="row align-items-center" style="min-height: 75vh;">
            <!-- Left Column: Branding Text -->
            <div class="col-lg-7 d-none d-lg-block">
                <h1 class="display-4 fw-bold text-white">Welcome!</h1>
                <p class="lead text-white mt-3">
                  <span style="font-weight: 600;">Your tasks and projects are waiting for you.</span>
                </p>
            </div>

            <!-- Right Column: Login Form Card -->
            <div class="col-lg-5">
                <div class="card login-card p-4 p-sm-5 shadow">
                  <h3 class="text-center mb-4">Employee Login</h3>
    
                  <?php if (!empty($loginError)): ?>
                    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($loginError); ?></div>
                  <?php endif; ?>
    
                  <form action="login.php" method="POST">
                    <div class="form-floating mb-3">
                      <input type="text" class="form-control" id="userid" name="userid" placeholder="Employee ID" required />
                      <label for="userid">Employee ID</label>
                    </div>
                    <div class="form-floating mb-3">
                      <input type="password" class="form-control" id="password" name="password" placeholder="Password" />
                      <label for="password">Password</label>
                    </div>
                    <div class="d-grid mt-4">
                      <button type="submit" class="btn btn-primary btn-lg">Login</button>
                    </div>
                  </form>
    
                  <div class="text-center mt-4">
                    <a href="#" class="link-secondary fw-bold" data-bs-toggle="modal" data-bs-target="#employeeListModal">
                      New Employee? Find your ID
                    </a>
                  </div>
                </div>
            </div>
        </div>
    </main>
  </div>

  <!-- 'Find Your ID' Modal (Unchanged structurally, but will inherit new styles) -->
  <div class="modal fade" id="employeeListModal" tabindex="-1" aria-labelledby="employeeListModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="employeeListModalLabel">Find Your Employee ID</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted small">Find your ID in the list below. New employees need to just enter Employee ID only for the first time.</p>
          
          <?php if (empty($employees_by_team)): ?>
            <p class="text-center">No employee records found.</p>
          <?php else: ?>
            <table class="table table-striped table-hover">
              <thead class="table-light">
                <tr><th>Employee ID</th><th>Full Name</th><th>Email</th></tr>
              </thead>
              <tbody>
                <?php foreach ($employees_by_team as $team_name => $employees): ?>
                  <tr class="table-group-divider">
                    <td colspan="3" class="fw-bold text-center bg-info"><?php echo htmlspecialchars($team_name); ?></td>
                  </tr>
                  <?php foreach ($employees as $employee): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($employee['employeeID']); ?></td>
                      <td><?php echo htmlspecialchars($employee['employeeFullName']); ?></td>
                      <td><?php echo htmlspecialchars($employee['employeeEmail']); ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>