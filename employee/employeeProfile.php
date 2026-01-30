<?php
session_start();
include '../config/database.php';

// --- Security Check ---
// Ensure the user is logged in and is an 'employee'
if (!isset($_SESSION['employeeID']) || $_SESSION['category'] !== 'employee') {
    header("Location: ../login.html");
    exit();
}

$employee_id = $_SESSION['employeeID'];
$employee_details = null;

// --- Fetch all details for the logged-in employee ---
// A LEFT JOIN is used so the page doesn't break if an employee isn't assigned to a team.
$sql = "SELECT 
            e.employeeID,
            e.employeeEmail,
            e.employeeFullName,
            e.employeeNoPhone,
            e.employeeDOB,
            e.employeeIC,
            e.employeePicture,
            e.employeeAddress,
            t.teamName
        FROM 
            employee e
        LEFT JOIN 
            team t ON e.teamID = t.teamID
        WHERE 
            e.employeeID = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $employee_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result) {
    $employee_details = mysqli_fetch_assoc($result);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

// Set a placeholder for the picture if it's missing or the file doesn't exist
$profile_picture_path = "../assets/profile-removebg-preview.png"; // Default placeholder
if (!empty($employee_details['employeePicture'])) {
    $potential_path = "../uploads/" . $employee_details['employeePicture'];
    if (file_exists($potential_path)) {
        $profile_picture_path = $potential_path;
    }
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/employee.css">
</head>

<body>

  <!-- NAVBAR & SIDEBAR (Consistent with other pages) -->
  <nav class="navbar bg-light">
    <div class="container-fluid d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">☰</button>
        <a class="navbar-brand mb-0" href="#"><img src="../assets/download-removebg-preview.png" height="24"></a>
      </div>
      <a class="d-flex align-items-center gap-2 text-decoration-none text-dark" href="employeeProfile.php">
        <span>WELCOME, <?php echo htmlspecialchars(strtoupper(isset($employee_details['employeeFullName']) ? $employee_details['employeeFullName'] : 'EMPLOYEE')); ?></span>
        <img src="<?php echo $profile_picture_path; ?>" alt="Profile Picture" class="rounded-circle" style="height: 32px; width: 32px; object-fit: cover;">
      </a>
    </div>
  </nav>

  <div class="offcanvas offcanvas-start bg-dark text-white d-flex flex-column" tabindex="-1" id="sidebarMenu" style="width:280px;">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title">Menu</h5>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column flex-grow-1 p-0">
      <ul class="nav flex-column flex-grow-1">
        <li class="nav-item mb-2"><a class="nav-link text-white" href="employee-dashboard.php">Dashboard</a></li>
        <li class="nav-item mb-2"><a class="nav-link text-white" href="myTask-employee.php">My Task</a></li>
        <!-- You can add a link to this profile page here -->
        <li class="nav-item mb-2"><a class="nav-link text-white active" href="employeeProfile.php">My Profile</a></li>
      </ul>
      <div class="mt-auto mb-3 px-3"><button class="btn btn-outline-light w-100 " data-bs-toggle="modal" data-bs-target="#logoutModal">LOG OUT</button></div>
    </div>
  </div>

  <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
        <div class="modal-body">Are you sure you want to log out?</div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><a href="../logout.php" class="btn btn-danger">Yes, Logout</a></div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <div class="container my-5">
    <div class="card shadow-sm">
      <div class="card-header bg-dark text-white">
        <h4 class="mb-0">My Profile Information</h4>
      </div>
      <div class="card-body p-4">
        <?php if ($employee_details): ?>
            <div class="row">
                <div class="col-md-4 text-center">
                    <img src="<?php echo $profile_picture_path; ?>" alt="Profile Picture" class="img-fluid rounded" style="height:270px">
                </div>
                <div class="col-md-8">
                    <h3><?php echo htmlspecialchars($employee_details['employeeFullName']); ?></h3>
                    <p class="text-muted"><?php echo htmlspecialchars(isset($employee_details['teamName']) ? $employee_details['teamName'] : 'Unassigned'); ?></p>
                    <hr>
                    <div class="row">
                        <div class="col-sm-4"><strong>Employee ID</strong></div>
                        <div class="col-sm-8"><?php echo htmlspecialchars($employee_details['employeeID']); ?></div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-4"><strong>Email Address</strong></div>
                        <div class="col-sm-8"><?php echo htmlspecialchars($employee_details['employeeEmail']); ?></div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-4"><strong>Phone Number</strong></div>
                        <div class="col-sm-8"><?php echo htmlspecialchars(isset($employee_details['employeeNoPhone']) ? $employee_details['employeeNoPhone'] : 'N/A'); ?></div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-4"><strong>Date of Birth</strong></div>
                        <div class="col-sm-8"><?php echo htmlspecialchars(isset($employee_details['employeeDOB']) ? $employee_details['employeeDOB'] : 'N/A'); ?></div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4"><strong>IC Number</strong></div>
                        <div class="col-sm-8"><?php echo htmlspecialchars(isset($employee_details['employeeIC']) ? $employee_details['employeeIC'] : 'N/A'); ?></div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-4"><strong>Address</strong></div>
                        <div class="col-sm-8"><?php echo nl2br(htmlspecialchars(isset($employee_details['employeeAddress']) ? $employee_details['employeeAddress'] : 'N/A')); ?></div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger" role="alert">
                Could not find your profile information. Please contact an administrator.
            </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>