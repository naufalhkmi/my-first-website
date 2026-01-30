<?php

session_start();

include '../config/database.php';

// --- SECTION 1: AUTHENTICATION & SECURITY CHECK ---
if (!isset($_SESSION['employeeID']) || !isset($_SESSION['teamID'])) {
    header('Location: ../login.php'); 
    exit();
}

// --- SECTION 2: DATA FETCHING ---
$employeeID = $_SESSION['employeeID'];
$teamID = (int)$_SESSION['teamID'];

// 1. Get the current employee's profile information.
$employee_info = [];
$stmt_profile = $conn->prepare("SELECT e.employeeFullName, e.employeePicture, e.roles, t.teamName FROM employee e LEFT JOIN team t ON e.teamID = t.teamID WHERE e.employeeID = ?");
$stmt_profile->bind_param("s", $employeeID);
$stmt_profile->execute();
$result_profile = $stmt_profile->get_result();
if ($user = $result_profile->fetch_assoc()) {
    $employee_info = $user;
}
$stmt_profile->close();

// 2. Get the counts of tasks for the employee's team.
$task_counts = ['not_started' => 0, 'in_progress' => 0, 'completed' => 0];
$sql_counts = "SELECT
            COUNT(CASE WHEN status = 'Not Started' THEN 1 END) AS not_started,
            COUNT(CASE WHEN status = 'In Progress' THEN 1 END) AS in_progress,
            COUNT(CASE WHEN status = 'Completed' THEN 1 END) AS completed
        FROM tasks WHERE teamID = ?";
$stmt_counts = $conn->prepare($sql_counts);
$stmt_counts->bind_param("i", $teamID);
$stmt_counts->execute();
$result_counts = $stmt_counts->get_result();
if ($row = $result_counts->fetch_assoc()) {
    $task_counts = $row;
}
$stmt_counts->close();

// 3. Get all calendar events (tasks with due dates) for the employee's team.
$calendar_events = [];
$sql_events = "SELECT taskName, dueDate, status FROM tasks WHERE teamID = ? AND dueDate IS NOT NULL";
$stmt_events = $conn->prepare($sql_events);
$stmt_events->bind_param("i", $teamID);
$stmt_events->execute();
$result_events = $stmt_events->get_result();
while ($row = $result_events->fetch_assoc()) {
    $color = '#ffc107';
    if ($row['status'] == 'Completed') $color = '#198754';
    if ($row['status'] == 'Not Started') $color = '#dc3545';
    $calendar_events[] = ['title' => $row['taskName'], 'start' => $row['dueDate'], 'color' => $color];
}
$stmt_events->close();

// 4. Get the Top 3 upcoming deadlines for the employee's team.
$upcoming_tasks = [];
$sql_upcoming = "SELECT taskName, dueDate FROM tasks WHERE teamID = ? AND status != 'Completed' AND dueDate >= CURDATE() ORDER BY dueDate ASC LIMIT 3";
$stmt_upcoming = $conn->prepare($sql_upcoming);
$stmt_upcoming->bind_param("i", $teamID);
$stmt_upcoming->execute();
$result_upcoming = $stmt_upcoming->get_result();
while ($row = $result_upcoming->fetch_assoc()) {
    $upcoming_tasks[] = $row;
}
$stmt_upcoming->close();
$conn->close();

// --- 5. Logic to determine the correct profile picture path ---
$profile_picture_path = "../assets/profile-removebg-preview.png"; // Default placeholder
if (!empty($employee_info['employeePicture'])) {
    $potential_path = "../uploads/" . $employee_info['employeePicture'];
    if (file_exists($potential_path)) {
        $profile_picture_path = $potential_path; // Use real picture if it exists
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/employee.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
  <style>
    .card { border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .card-header { background-color: #fff; font-weight: 500; border-bottom: 1px solid #f0f0f0; }
    #taskChartContainer { height: 280px; }
    #employeeCalendar .fc-header-toolbar { font-size: 0.85rem; }
    .profile-pic { width: 80px; height: 80px; object-fit: cover; }
    .deadline-date { font-size: 0.9em; }
    .btn:hover {background-color: black; color: white;}
  </style>
</head>
<body>
  
<!-- NAVBAR, SIDEBAR, and LOGOUT MODAL (Standard navigation elements) -->
<nav class="navbar bg-light">
  <div class="container-fluid d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-2">
      <button class="btn btn-outline--dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">☰</button>
      <a class="navbar-brand mb-0" href="#"><img src="../assets/download-removebg-preview.png" height="24"></a>
    </div>
    <a class="d-flex align-items-center gap-2 text-decoration-none text-dark" href="employeeProfile.php">
        <span>WELCOME, <?php echo htmlspecialchars(strtoupper(isset($employee_info['employeeFullName']) ? $employee_info['employeeFullName'] : 'EMPLOYEE')); ?></span>
        <img src="<?php echo $profile_picture_path; ?>" alt="Profile Picture" class="rounded-circle" style="height: 32px; width: 32px; object-fit: cover;">
    </a>
  </div>
</nav>
<div class="offcanvas offcanvas-start bg-dark text-white d-flex flex-column" tabindex="-1" id="sidebarMenu" style="width:280px;">
  <div class="offcanvas-header"><h5 class="offcanvas-title">Menu</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button></div>
  <div class="offcanvas-body d-flex flex-column flex-grow-1 p-0">
    <ul class="nav flex-column flex-grow-1">
      <li class="nav-item mb-2"><a class="nav-link text-white" href="employee-dashboard.php">Dashboard</a></li>
      <li class="nav-item mb-2"><a class="nav-link text-white" href="myTask-employee.php">My Task</a></li>
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
<div class="container py-4">
  <div class="row mb-4">
    <!-- Profile Card -->
    <div class="col-lg-4 mb-4">
      <div class="card h-100">
        <div class="card-body text-center d-flex flex-column justify-content-center">
        
        <!-- ########## THIS IS THE CORRECTED LINE ########## -->
        <img src="<?php echo $profile_picture_path; ?>" class="rounded-circle mx-auto profile-pic" alt="Profile Picture">
        
        <h5 class="mt-3 mb-0"><?= htmlspecialchars(isset($employee_info['employeeFullName']) ? $employee_info['employeeFullName'] : 'Employee Name') ?></h5>
        <p class="text-muted mb-1"><?= htmlspecialchars(isset($employee_info['roles']) ? $employee_info['roles'] : 'Role') ?></p>
        <span class="badge bg-info-subtle text-info-emphasis rounded-pill align-self-center"><?= htmlspecialchars(isset($employee_info['teamName']) ? $employee_info['teamName'] : 'No Team') ?></span>
       </div>
      </div>
    </div>
    <!-- Task Summary Card -->
    <div class="col-lg-4 mb-4">
      <div class="card h-100">
        <div class="card-header"><i class="bi bi-list-check me-2"></i>Task Summary</div>
        <div class="card-body d-flex flex-column justify-content-center">
          <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between align-items-center">Not Started <span class="badge bg-danger rounded-pill"><?= $task_counts['not_started'] ?></span></li>
            <li class="list-group-item d-flex justify-content-between align-items-center">In Progress <span class="badge bg-warning rounded-pill"><?= $task_counts['in_progress'] ?></span></li>
            <li class="list-group-item d-flex justify-content-between align-items-center">Completed <span class="badge bg-success rounded-pill"><?= $task_counts['completed'] ?></span></li>
          </ul>
        </div>
      </div>
    </div>
    <!-- Upcoming Deadlines Card -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-clock-history me-2"></i>Upcoming Deadlines</div>
            <div class="card-body">
                <?php if (empty($upcoming_tasks)): ?>
                    <p class="text-center text-muted mt-3">No upcoming deadlines. Great job!</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($upcoming_tasks as $task): ?>
                            <li class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <p class="mb-1 fw-bold"><?= htmlspecialchars($task['taskName']) ?></p>
                                    <small class="text-danger deadline-date"><?= date('M j', strtotime($task['dueDate'])) ?></small>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
  </div>
  <!-- Row Two: Contains the Calendar and the Chart -->
  <div class="row">
    <div class="col-lg-7 mb-4">
      <div class="card h-100">
        <div class="card-header"><i class="bi bi-calendar-event me-2"></i>Team Calendar</div>
        <div class="card-body d-flex flex-column">
            <div class="flex-grow-1" id="employeeCalendar"></div>
        </div>
      </div>
    </div>
    <div class="col-lg-5 mb-4">
      <div class="card h-100">
        <div class="card-header"><i class="bi bi-bar-chart-line me-2"></i>Task Status Chart</div>
        <div class="card-body d-flex align-items-center">
         <div id="taskChartContainer" class="w-100">
            <canvas id="taskBarChart"></canvas>
         </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('employeeCalendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today', center: 'title', right: 'dayGridMonth,listWeek'
        },
        events: <?= json_encode($calendar_events) ?>,
        height: '100%'
    });
    calendar.render();
    const ctx = document.getElementById('taskBarChart').getContext('2d');
    const taskData = <?= json_encode($task_counts) ?>;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Not Started', 'In Progress', 'Completed'],
            datasets: [{
                label: 'Number of Tasks',
                data: [taskData.not_started, taskData.in_progress, taskData.completed],
                backgroundColor: ['rgba(220, 53, 69, 0.7)','rgba(255, 193, 7, 0.7)','rgba(25, 135, 84, 0.7)'],
                borderColor: ['#dc3545','#ffc107','#198754'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
});
</script>
</body>
</html>