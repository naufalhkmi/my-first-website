<?php

session_start();
include '../config/database.php';



$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : 'All';


if (isset($_GET['delete_id'])) {
    $id_to_delete = (int)$_GET['delete_id'];

    $sql_delete = "DELETE FROM tasks WHERE taskID = $id_to_delete";
    if (mysqli_query($conn, $sql_delete)) {

        header("Location: task-management.php?status_filter=" . urlencode($status_filter)); exit();
    } else { echo "Error deleting record: " . mysqli_error($conn); }
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['task_id_edit'])) {
        $taskID = (int)$_POST['task_id_edit'];
        $taskName = trim($_POST['taskName_edit']);
        $location = trim($_POST['location_edit']);
        $taskDescription = trim($_POST['taskDescription_edit']);
        $assignedTeamID = (int)$_POST['assignedTeam_edit'];
        $dueDate = $_POST['dueDate_edit'];
        $dueDateValue = !empty($dueDate) ? "'$dueDate'" : "NULL";
        $sql_update = "UPDATE tasks SET taskName = '$taskName', location1 = '$location', taskDesc = '$taskDescription', teamID = $assignedTeamID, dueDate = $dueDateValue WHERE taskID = $taskID";
        if (mysqli_query($conn, $sql_update)) {
            header("Location: task-management.php?status_filter=" . urlencode($status_filter)); exit();
        } else { echo "Error updating record: " . mysqli_error($conn); }


    } else if (isset($_POST['taskName'])) {

        $taskName = trim($_POST['taskName']);
        $location = trim($_POST['location']);
        $taskDescription = trim($_POST['taskDescription']);
        $assignedTeamID = (int)$_POST['assignedTeam'];
        $dueDate = $_POST['dueDate'];
        $dueDateValue = !empty($dueDate) ? "'$dueDate'" : "NULL";
        $sql_insert = "INSERT INTO tasks (taskName, location1, taskDesc, teamID, dueDate) VALUES ('$taskName', '$location', '$taskDescription', $assignedTeamID, $dueDateValue)";
        if (mysqli_query($conn, $sql_insert)) {
            header("Location: task-management.php?status_filter=All"); exit();
        } else { echo "Error creating record: " . mysqli_error($conn); }
    }
}


$teams_list = [];
$sql_teams = "SELECT teamID, teamName FROM team ORDER BY teamName ASC";
$result_teams = mysqli_query($conn, $sql_teams);
if ($result_teams) {
    while($row = mysqli_fetch_assoc($result_teams)) {
        $teams_list[] = $row;
    }
}


$team_members = [];
$sql_employees = "SELECT teamID, employeeID, employeeFullName, employeeNoPhone FROM employee WHERE teamID IS NOT NULL ORDER BY employeeFullName ASC";
$result_employees = mysqli_query($conn, $sql_employees);
if ($result_employees) {
    while ($employee = mysqli_fetch_assoc($result_employees)) {
        if (!isset($team_members[$employee['teamID']])) {
            $team_members[$employee['teamID']] = [];
        }
        $team_members[$employee['teamID']][] = ['id' => $employee['employeeID'], 'name' => $employee['employeeFullName'], 'phone' => $employee['employeeNoPhone']];
    }
}


$tasks = [];
$sql_tasks = "SELECT 
                tasks.taskID, tasks.location1, tasks.taskName, tasks.taskDesc, tasks.teamID, 
                tasks.createdAt, tasks.dueDate, tasks.status,
                team.teamName AS assignedTeam
              FROM tasks
              JOIN team ON tasks.teamID = team.teamID";


if ($status_filter !== 'All') {

    $sql_tasks .= " WHERE tasks.status = ?";
}

$sql_tasks .= " ORDER BY tasks.createdAt DESC";



$stmt_tasks = mysqli_prepare($conn, $sql_tasks);


if ($status_filter !== 'All') {

    mysqli_stmt_bind_param($stmt_tasks, "s", $status_filter);
}


mysqli_stmt_execute($stmt_tasks);
$result_tasks = mysqli_stmt_get_result($stmt_tasks);

if ($result_tasks) {
    while ($row = mysqli_fetch_assoc($result_tasks)) {
        $tasks[] = $row;
    }
}
mysqli_stmt_close($stmt_tasks);
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Task Management</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../css/admin.css">
  <style>
    .rounded-table { border-collapse: separate; border-spacing: 0; border-radius: 12px; overflow: hidden; }
    .action-buttons a, .action-buttons button { margin-right: 5px; }
    .team-name-link { cursor: pointer; text-decoration: underline; text-decoration-style: dotted; color: #0d6efd; }
  </style>
</head>
<body>


<nav class="navbar bg-light">
    <div class="container-fluid d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">☰</button>
        <a class="navbar-brand mb-0" href="#"><img src="../assets/download-removebg-preview.png" height="24" alt="Logo"></a>
      </div>
      <a class="d-flex align-items-center gap-2 text-decoration-none text-white" href="#">
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
        <div class="modal-header"><h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
        <div class="modal-body">Are you sure you want to log out?</div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><a href="../logout.php" class="btn btn-danger">Yes, Logout</a></div>
      </div>
    </div>
</div>

<div class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTaskModal">
      + Create New Task
    </button>
    
    <form method="GET" action="task-management.php" class="d-flex align-items-center">
        <label for="status_filter" class="form-label me-2 mb-0 text-white">Status:</label>
        <select name="status_filter" id="status_filter" class="form-select" onchange="this.form.submit()">
            <option value="All" <?php if($status_filter == 'All') echo 'selected'; ?>>All</option>
            <option value="Not Started" <?php if($status_filter == 'Not Started') echo 'selected'; ?>>Not Started</option>
            <option value="In Progress" <?php if($status_filter == 'In Progress') echo 'selected'; ?>>In Progress</option>
            <option value="Completed" <?php if($status_filter == 'Completed') echo 'selected'; ?>>Completed</option>
        </select>
    </form>
  </div>

  <div class="container mt-4 px-0">
    <table class="table table-bordered rounded-table table-striped align-middle">
      <thead class="table-dark">
        <tr>
          <th style="width: 2%;">No</th>
          <th style="width: 15%;">Task</th>
          <th style="width: 15%;">Location</th>
          <th style="width: 25%;">Description</th>
          <th style="width: 7%;">Assigned</th>
          <th style="width: 10%;">Created</th>
          <th style="width: 10%;">Due Date</th>
          <th style="width: 20%;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($tasks)): ?>
            <tr><td colspan="8" class="text-center">No tasks found for the selected status.</td></tr>
        <?php else: ?>
            <?php foreach ($tasks as $index => $task): ?>
            <tr>
                <td><?php echo $index + 1; ?></td>
                <td><?php echo htmlspecialchars($task['taskName']); ?></td>
                <td><?php echo htmlspecialchars($task['location1']); ?></td>
                <td><?php echo htmlspecialchars($task['taskDesc']); ?></td>
                <td>
                    <?php
                        $current_team_id = $task['teamID'];
                        $members_for_this_team = isset($team_members[$current_team_id]) ? $team_members[$current_team_id] : [];
                    ?>
                    <span class="team-name-link" data-bs-toggle="modal" data-bs-target="#teamMembersModal" data-team-name="<?php echo htmlspecialchars($task['assignedTeam']); ?>" data-members='<?php echo json_encode($members_for_this_team); ?>'>
                        <?php echo htmlspecialchars($task['assignedTeam']); ?>
                    </span>
                </td>
                <td><?php echo date('Y-m-d', strtotime($task['createdAt'])); ?></td>
                <td><?php echo isset($task['dueDate']) ? $task['dueDate'] : 'N/A'; ?></td>
                <td class="action-buttons">
                    <button type="button" class="btn btn-sm btn-warning edit-btn" data-bs-toggle="modal" data-bs-target="#editTaskModal" data-id="<?php echo $task['taskID']; ?>" data-name="<?php echo htmlspecialchars($task['taskName']); ?>" data-location="<?php echo htmlspecialchars($task['location1']); ?>" data-desc="<?php echo htmlspecialchars($task['taskDesc']); ?>" data-team-id="<?php echo $task['teamID']; ?>" data-due="<?php echo $task['dueDate']; ?>">Edit</button>
                    <a href="task-management.php?delete_id=<?php echo $task['taskID']; ?>&status_filter=<?php echo urlencode($status_filter); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this task?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="createTaskModal" tabindex="-1" aria-labelledby="createTaskLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="task-management.php" class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="createTaskLabel">Create New Task</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="mb-3"><label class="form-label">Task Name <span class="text-danger">*</span></label><input type="text" name="taskName" class="form-control" required /></div>
        <div class="mb-3"><label class="form-label">Location <span class="text-danger">*</span></label><input type="text" name="location" class="form-control" required /></div>
        <div class="mb-3"><label class="form-label">Task Description <span class="text-danger">*</span></label><textarea name="taskDescription" class="form-control" rows="3" required></textarea></div>
        <div class="mb-3">
          <label class="form-label">Assign to Team <span class="text-danger">*</span></label>
          <select name="assignedTeam" class="form-select" required>
            <option value="">-- Select Team --</option>
            <?php foreach($teams_list as $team): ?>
              <option value="<?php echo $team['teamID']; ?>"><?php echo htmlspecialchars($team['teamName']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3"><label class="form-label">Due Date</label><input type="date" name="dueDate" class="form-control" /></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Task</button></div>
    </form>
  </div>
</div>

<div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="task-management.php" class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="editTaskLabel">Edit Task</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <input type="hidden" name="task_id_edit" id="edit_task_id">
        <div class="mb-3"><label class="form-label">Task Name <span class="text-danger">*</span></label><input type="text" name="taskName_edit" id="edit_taskName" class="form-control" required /></div>
        <div class="mb-3"><label class="form-label">Location <span class="text-danger">*</span></label><input type="text" name="location_edit" id="edit_location" class="form-control" required /></div>
        <div class="mb-3"><label class="form-label">Task Description <span class="text-danger">*</span></label><textarea name="taskDescription_edit" id="edit_taskDescription" class="form-control" rows="3" required></textarea></div>
        <div class="mb-3">
          <label class="form-label">Assign to Team <span class="text-danger">*</span></label>
          <select name="assignedTeam_edit" id="edit_assignedTeam" class="form-select" required>
            <option value="">-- Select Team --</option>
            <?php foreach($teams_list as $team): ?>
              <option value="<?php echo $team['teamID']; ?>"><?php echo htmlspecialchars($team['teamName']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3"><label class="form-label">Due Date</label><input type="date" name="dueDate_edit" id="edit_dueDate" class="form-control" /></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Changes</button></div>
    </form>
  </div>
</div>

<div class="modal fade" id="teamMembersModal" tabindex="-1" aria-labelledby="teamMembersModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="teamMembersModalLabel">Team Members</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body" id="teamMembersModalBody"></div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>

document.addEventListener('DOMContentLoaded', function () {


  const editButtons = document.querySelectorAll('.edit-btn');
  const taskIdInput = document.getElementById('edit_task_id');
  const taskNameInput = document.getElementById('edit_taskName');
  const locationInput = document.getElementById('edit_location');
  const taskDescInput = document.getElementById('edit_taskDescription');
  const assignedTeamSelect = document.getElementById('edit_assignedTeam');
  const dueDateInput = document.getElementById('edit_dueDate');
  
  editButtons.forEach(function (button) {
    button.addEventListener('click', function () {

      taskIdInput.value = this.getAttribute('data-id');
      taskNameInput.value = this.getAttribute('data-name');
      locationInput.value = this.getAttribute('data-location'); 
      taskDescInput.value = this.getAttribute('data-desc');
      assignedTeamSelect.value = this.getAttribute('data-team-id');
      dueDateInput.value = this.getAttribute('data-due');
    });
  });


  const teamModal = document.getElementById('teamMembersModal');
  teamModal.addEventListener('show.bs.modal', event => {
    const triggerLink = event.relatedTarget; 
    const teamName = triggerLink.getAttribute('data-team-name');
    const members = JSON.parse(triggerLink.getAttribute('data-members')); 
    const modalTitle = teamModal.querySelector('.modal-title');
    const modalBody = teamModal.querySelector('.modal-body');
    
    modalTitle.textContent = teamName + ' Members';
    modalBody.innerHTML = '';
    
    if (members.length > 0) {
      const list = document.createElement('ul');
      list.classList.add('list-group');
      members.forEach(member => {
        const listItem = document.createElement('li');
        listItem.classList.add('list-group-item');
        listItem.innerHTML = `<strong>${member.name}</strong><br><small class="text-muted">ID: ${member.id} | Phone: ${member.phone || 'N/A'}</small>`;
        list.appendChild(listItem);
      });
      modalBody.appendChild(list);
    } else {
      modalBody.innerHTML = '<p>There are no members assigned to this team.</p>';
    }
  });
});
</script>
</body>
</html>