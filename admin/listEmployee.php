<?php
// Add these two lines at the very top of your PHP script for robust error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection file.
require_once '../config/database.php';


// --- FINALIZED: SECTION TO HANDLE DELETE REQUEST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_employee_id'])) {
    $employee_id_to_delete = $_POST['delete_employee_id'];

    // Use a transaction to ensure all or nothing is deleted.
    $conn->begin_transaction();

    try {
        // STEP 1: Get picture filename BEFORE deleting the employee record
        $picture_file = null;
        $pic_query = $conn->prepare("SELECT employeePicture FROM employee WHERE employeeID = ?");
        $pic_query->bind_param("s", $employee_id_to_delete);
        $pic_query->execute();
        $pic_result = $pic_query->get_result();
        if ($pic_row = $pic_result->fetch_assoc()) {
            $picture_file = $pic_row['employeePicture'];
        }
        $pic_query->close();

        // STEP 2: Delete from the 'users' table FIRST (to satisfy foreign key).
        $delete_user_stmt = $conn->prepare("DELETE FROM users WHERE userID = ?");
        $delete_user_stmt->bind_param("s", $employee_id_to_delete);
        $delete_user_stmt->execute();
        $delete_user_stmt->close();

        // STEP 3: Delete from the 'employee' table.
        $delete_employee_stmt = $conn->prepare("DELETE FROM employee WHERE employeeID = ?");
        $delete_employee_stmt->bind_param("s", $employee_id_to_delete);
        $delete_employee_stmt->execute();
        $delete_employee_stmt->close();

        // STEP 4: If database deletions were successful, commit and delete the file.
        $conn->commit();
        if (!empty($picture_file) && file_exists("../uploads/" . $picture_file)) {
            unlink("../uploads/" . $picture_file);
        }

        // Redirect to this same page to show the updated list.
        header("Location: " . $_SERVER['PHP_SELF'] . "?delete_success=1");
        exit();

    } catch (mysqli_sql_exception $e) {
        // If any error occurred, roll back all database changes.
        $conn->rollback();
        
        // Display the exact error message and stop the script.
        die("<strong>Database Deletion Failed!</strong><br><br>Error: " . $e->getMessage());
    }
}


// --- SECTION 1: DATA PREPARATION (Unchanged) ---
$teams_query = "SELECT teamID, teamName FROM team ORDER BY teamName ASC";
$teams_result = $conn->query($teams_query);
$teams = [];
while ($row = $teams_result->fetch_assoc()) {
    $teams[] = $row;
}
$employees_by_team = [];
$employees_query = "SELECT employee.*, team.teamName FROM employee LEFT JOIN team ON employee.teamID = team.teamID ORDER BY employee.employeeFullName ASC";
$employees_result = $conn->query($employees_query);
while ($row = $employees_result->fetch_assoc()) {
    $team_id = !empty($row['teamID']) ? $row['teamID'] : 'unassigned';
    $employees_by_team[$team_id][] = $row;
}
$logo_map = [
    'Team A' => '../assets/letter-a (1).png',
    'Team B' => '../assets/letter-b.png',
    'Team C' => '../assets/letter-c.png',
    'Team D' => '../assets/letter-d.png',
    'Team E' => '../assets/letter-e.png',
];
$default_logo = '../assets/teams/default-logo.png';

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Employees</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/admin.css">
  
  <style>
    .card-hover { transition: transform 0.3s ease, box-shadow 0.3s ease; }
    .card-hover:hover { transform: translateY(-10px); box-shadow: 0 10px 20px rgba(0,0,0,0.12); }
    .team-logo { max-height: 120px; width: auto; object-fit: contain; margin-bottom: 1rem; }
    .card-title { font-weight: 600; font-size: 1.5rem; }
  </style>
</head>
<body>
  
<!-- NAVBAR, SIDEBAR, LOGOUT MODAL (Unchanged) -->
<nav class="navbar bg-light">
  <div class="container-fluid d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-2">
      <button class="btn btn-outline-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">☰</button>
      <a class="navbar-brand mb-0" href="#"><img src="../assets/download-removebg-preview.png" height="24" alt="Logo"></a>
    </div>
    <a class="d-flex align-items-center gap-2 text-decoration-none text-dark" href="#">
      <span>Hi Admin</span>
      <img src="../assets/profile-removebg-preview.png" height="32" alt="Profile">
    </a>
  </div>
</nav>
<div class="offcanvas offcanvas-start bg-dark text-white d-flex flex-column" tabindex="-1" id="sidebarMenu" style="width:280px;">
  <div class="offcanvas-header"><h5 class="offcanvas-title">Menu</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button></div>
  <div class="offcanvas-body d-flex flex-column flex-grow-1 p-0">
    <ul class="nav flex-column flex-grow-1">
      <li class="nav-item mb-2"><a class="nav-link text-white" href="dashboard.php">Dashboard</a></li>
      <li class="nav-item mb-2"><a class="nav-link text-white" href="task-management.php">Task Management</a></li>
      <li class="nav-item mb-2"><a class="nav-link text-white" href="progress.php">Progress Tracker</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="manageEmployee.php">Manage Employee</a></li>
    </ul>
    <div class="mt-auto mb-3 px-3"><button class="btn btn-outline-light w-100" data-bs-toggle="modal" data-bs-target="#logoutModal">LOG OUT</button></div>
  </div>
</div>
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">Are you sure you want to log out?</div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><a href="../logout.php" class="btn btn-danger">Yes, Logout</a></div>
    </div>
  </div>
</div>

<!-- Main Content Area -->
<div class="container my-5">
  <div class="container mt-4 mb-4 d-flex">
    <div class="card mx-auto text-center" style="width: 300px; height: 30px;">
      <strong>TEAMS</strong>
    </div>
  </div>
  <!-- This is where we create the grid of team cards. -->
  <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 justify-content-center">
    
    <?php foreach ($teams as $team): 
      $team_id = $team['teamID'];
      $team_name = $team['teamName'];
      $employee_count = isset($employees_by_team[$team_id]) ? count($employees_by_team[$team_id]) : 0;
      $logo_src = isset($logo_map[$team_name]) ? $logo_map[$team_name] : $default_logo;
    ?>
      <div class="col">
        <div class="card h-100 text-center shadow-sm card-hover">
          <div class="card-body d-flex flex-column p-4">
            <img src="<?= htmlspecialchars($logo_src) ?>" alt="<?= htmlspecialchars($team_name) ?> Logo" class="team-logo align-self-center">
            <h5 class="card-title"><?= htmlspecialchars($team_name) ?></h5>
            <p class="card-text text-muted">Contains <?= $employee_count ?> member(s)</p>
            <div class="mt-auto">
              <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#teamModal-<?= $team_id ?>">View List</button>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

    <?php if (!empty($employees_by_team['unassigned'])): 
        $unassigned_count = count($employees_by_team['unassigned']);
    ?>
      <div class="col">
        <div class="card h-100 text-center shadow-sm card-hover border-secondary">
          <div class="card-body d-flex flex-column p-4">
            <i class="bi bi-person-x-fill fs-1 text-secondary align-self-center" style="font-size: 7rem !important;"></i>
            <h5 class="card-title text-secondary">Unassigned</h5>
            <p class="card-text text-muted">Contains <?= $unassigned_count ?> member(s)</p>
            <div class="mt-auto">
                <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#teamModal-unassigned">View List</button>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- SECTION 3: ALL MODALS (Hidden by default) -->

<!-- Team List Modals -->
<?php foreach ($teams as $team): 
  $team_id = $team['teamID'];
  $employees_in_team = isset($employees_by_team[$team_id]) ? $employees_by_team[$team_id] : [];
?>
  <div class="modal fade" id="teamModal-<?= $team_id ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Employees in: <?= htmlspecialchars($team['teamName']) ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <?php if (empty($employees_in_team)): ?>
            <p class="text-center">There are no employees assigned to this team.</p>
          <?php else: ?>
            <table class="table table-striped align-middle">
              <thead><tr><th>No</th><th>Picture</th><th>Full Name</th><th>Phone</th><th>Email</th><th>Action</th></tr></thead>
              <tbody>
                <?php foreach ($employees_in_team as $index => $employee): ?>
                  <tr>
                    <td><?= $index + 1 ?></td>
                    <td><img src="../uploads/<?= htmlspecialchars($employee['employeePicture']) ?>" alt="Profile" style="width: 60px; height: 60px; object-fit: cover; border-radius: 50%;"></td>
                    <td><?= htmlspecialchars($employee['employeeFullName']) ?></td>
                    <td><?= htmlspecialchars($employee['employeeNoPhone']) ?></td>
                    <td><?= htmlspecialchars($employee['employeeEmail']) ?></td>
                    <td><button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal-<?= htmlspecialchars($employee['employeeID']) ?>">View Details</button></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
<?php endforeach; ?>

<!-- Unassigned Employees Modal -->
<?php if (!empty($employees_by_team['unassigned'])): ?>
  <div class="modal fade" id="teamModal-unassigned" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Unassigned Employees</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <table class="table table-striped align-middle">
                    <thead><tr><th>No</th><th>Picture</th><th>Full Name</th><th>Phone</th><th>Email</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach ($employees_by_team['unassigned'] as $index => $employee): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><img src="../uploads/<?= htmlspecialchars($employee['employeePicture']) ?>" alt="Profile" style="width: 60px; height: 60px; object-fit: cover; border-radius: 50%;"></td>
                            <td><?= htmlspecialchars($employee['employeeFullName']) ?></td>
                            <td><?= htmlspecialchars($employee['employeeNoPhone']) ?></td>
                            <td><?= htmlspecialchars($employee['employeeEmail']) ?></td>
                            <td><button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal-<?= htmlspecialchars($employee['employeeID']) ?>">View Details</button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
  </div>
<?php endif; ?>

<!-- Employee Details Modals -->
<?php if ($employees_result->num_rows > 0) {
    $employees_result->data_seek(0);
    while ($employee = $employees_result->fetch_assoc()):
?>
  <div class="modal fade" id="viewModal-<?= htmlspecialchars($employee['employeeID']) ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Employee Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-4 text-center"><img src="../uploads/<?= htmlspecialchars($employee['employeePicture']) ?>" alt="Profile" class="img-fluid rounded"></div>
            <div class="col-md-8">
              <p><strong>Full Name:</strong> <?= htmlspecialchars($employee['employeeFullName']) ?></p>
              <p><strong>Employee ID:</strong> <?= htmlspecialchars($employee['employeeID']) ?></p>
              <p><strong>Email:</strong> <?= htmlspecialchars($employee['employeeEmail']) ?></p>
              <p><strong>Phone:</strong> <?= htmlspecialchars($employee['employeeNoPhone']) ?></p>
              <p><strong>Date of Birth:</strong> <?= htmlspecialchars($employee['employeeDOB']) ?></p>
              <p><strong>IC Number:</strong> <?= htmlspecialchars($employee['employeeIC']) ?></p>
              <p><strong>Address:</strong> <?= nl2br(htmlspecialchars($employee['employeeAddress'])) ?></p>
              <p><strong>Role:</strong> <?= htmlspecialchars($employee['roles']) ?></p>
              <p><strong>Team:</strong> <?= isset($employee['teamName']) ? $employee['teamName'] : 'Unassigned' ?></p>
            </div>
          </div>
        </div>
        <div class="modal-footer justify-content-between">
            <form method="POST" action="" onsubmit="return confirm('Are you sure you want to permanently remove this employee? This will also delete their login account and cannot be undone.');">
                <input type="hidden" name="delete_employee_id" value="<?= htmlspecialchars($employee['employeeID']) ?>">
                <button type="submit" class="btn btn-danger">Remove Employee</button>
            </form>
            <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
<?php 
    endwhile; 
  }
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>