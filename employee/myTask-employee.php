<?php

session_start();

include '../config/database.php';

// --- SECTION 1: AUTHENTICATION & SECURITY CHECK ---

if (!isset($_SESSION['employeeID']) || $_SESSION['category'] !== 'employee') {
    // If they are not a logged-in employee, redirect them to the login page.
    header("Location: ../login.html");
    exit(); // Stop the script from running any further.
}


// --- SECTION 2: INITIALIZE VARIABLES ---

// Get the logged-in employee's information from the session.
$employee_id = $_SESSION['employeeID'];
// Check if the user's role is set in the session. If not, default to an empty string.
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : '';

// Create empty containers for the data we will fetch and display.
$employee_name = '';
$my_tasks = [];
// This variable will hold any success or error messages after a form is submitted.
$update_message = '';


// --- SECTION 3: HANDLE ALL FORM SUBMISSIONS (UPDATE, UPLOAD, DELETE) ---

// This entire block of code only runs IF the page received data from a submitted form (using the POST method).
// We also check that 'taskID' was submitted, as all our forms on this page relate to a specific task.
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['taskID'])) {
    
    // Get the ID of the task that the action was performed on.
    $task_id_to_update = (int)$_POST['taskID'];

    // --- A. Handle the "Delete My Proofs" Form Submission ---
    // We know this form was submitted if the 'delete_my_proofs' button was clicked.
    if (isset($_POST['delete_my_proofs'])) {
        
        // --- This is a two-step deletion process for proofs ---
        
        // Step 1: Get the filenames of the proofs we are about to delete, so we can remove the actual files from the server.
        // We use a prepared statement to be secure. We only select proofs for THIS task uploaded by THIS employee.
        $sql_get_proofs = "SELECT fileName FROM task_proofs WHERE taskID = ? AND uploadedBy = ?";
        $stmt_get = mysqli_prepare($conn, $sql_get_proofs);
        mysqli_stmt_bind_param($stmt_get, "is", $task_id_to_update, $employee_id);
        mysqli_stmt_execute($stmt_get);
        $result_proofs = mysqli_stmt_get_result($stmt_get);
        // Loop through each proof found.
        while ($row = mysqli_fetch_assoc($result_proofs)) {
            // Build the full path to the image file.
            $filepath = "../uploads/proofs/" . $row['fileName'];
            // If the file exists, delete it from the server's storage.
            if (file_exists($filepath)) { unlink($filepath); }
        }
        mysqli_stmt_close($stmt_get);
        
        // Step 2: Now that the files are gone, delete the records from the database.
        // Again, we use a prepared statement to delete only proofs for this task by this employee.
        $sql_delete = "DELETE FROM task_proofs WHERE taskID = ? AND uploadedBy = ?";
        $stmt_delete = mysqli_prepare($conn, $sql_delete);
        mysqli_stmt_bind_param($stmt_delete, "is", $task_id_to_update, $employee_id);
        if (mysqli_stmt_execute($stmt_delete)) {
            $update_message = "Your submitted proofs for this task have been deleted.";
        } else {
            $update_message = "Error deleting your proofs from the database.";
        }
        mysqli_stmt_close($stmt_delete);
    }
    // --- B. Handle the "Upload Proof" Form Submission ---
    // We know this form was submitted if a file was sent (`isset($_FILES['proof_file'])`).
    else if (isset($_FILES['proof_file']) && $_FILES['proof_file']['error'] == 0) {
        $proof_file = $_FILES['proof_file']; // The `$_FILES` superglobal holds all info about uploaded files.
        $target_dir = "../uploads/proofs/"; // The folder where we will save the proofs.
        
        // Create a unique filename to prevent overwriting other files.
        // We combine "proof_", the task ID, and the current time.
        $file_extension = strtolower(pathinfo($proof_file['name'], PATHINFO_EXTENSION));
        $unique_filename = "proof_" . $task_id_to_update . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $unique_filename;
        
        // Security: Check if the file type is one we allow.
        $allowed_types = ['jpg', 'png', 'jpeg'];
        if (in_array($file_extension, $allowed_types)) {
            // `move_uploaded_file` moves the temporary file to our final destination.
            if (move_uploaded_file($proof_file['tmp_name'], $target_file)) {
                // If the file moved successfully, insert a record into the database.
                $sql_insert_proof = "INSERT INTO task_proofs (taskID, fileName, uploadedBy) VALUES (?, ?, ?)";
                $stmt_insert = mysqli_prepare($conn, $sql_insert_proof);
                mysqli_stmt_bind_param($stmt_insert, "iss", $task_id_to_update, $unique_filename, $employee_id);
                if(mysqli_stmt_execute($stmt_insert)){
                   $update_message = "New proof uploaded successfully.";
                }
                mysqli_stmt_close($stmt_insert);
            } else { $update_message = "Error: Issue uploading file."; }
        } else { $update_message = "Error: Only JPG, JPEG, & PNG files are allowed."; }
    }
    // --- C. Handle the "Set Status" Form Submission ---
    // This form is only visible to the 'Leader', so we check their role again for security.
    else if ($user_role === 'Leader' && isset($_POST['status'])) {
        $new_status = $_POST['status'];
        
        // This is a more advanced way to build a query. We start with an array of query parts.
        $updates = ["status = ?"];
        $params = [$new_status];
        $types = 's'; // 's' for the status string.
        
        // If the leader is setting the status to 'Completed', we also want to record the completion time.
        if ($new_status === 'Completed') {
            $updates[] = "updatedAt = NOW()"; // NOW() is a MySQL function that gets the current date and time.
        }
        
        // `implode` joins the parts of our query together with a comma.
        // This dynamically creates "UPDATE tasks SET status = ?" or "UPDATE tasks SET status = ?, updatedAt = NOW()".
        $sql_update_task = "UPDATE tasks SET " . implode(", ", $updates) . " WHERE taskID = ?";
        
        // We add the task ID to our parameters list.
        $params[] = $task_id_to_update;
        $types .= 'i'; // Add 'i' for the integer task ID.
        
        // Execute the prepared statement with our dynamically built parameters.
        $stmt_update_task = mysqli_prepare($conn, $sql_update_task);
        mysqli_stmt_bind_param($stmt_update_task, $types, ...$params);
        if (mysqli_stmt_execute($stmt_update_task)) {
             $update_message = "Task status updated successfully.";
        }
        mysqli_stmt_close($stmt_update_task);
    }
}


// --- SECTION 4: DATA FETCHING FOR PAGE DISPLAY ---
// This runs every time the page loads to get the most up-to-date information.

// 1. Get the current employee's name and team ID.
$sql_employee_info = "SELECT employeeFullName, teamID FROM employee WHERE employeeID = ?";
$stmt = mysqli_prepare($conn, $sql_employee_info);
mysqli_stmt_bind_param($stmt, "s", $employee_id);
mysqli_stmt_execute($stmt);
$result_employee_info = mysqli_stmt_get_result($stmt);

// 2. If we found the employee, get their team's tasks.
if ($employee_info = mysqli_fetch_assoc($result_employee_info)) {
    $employee_name = $employee_info['employeeFullName'];
    $team_id = $employee_info['teamID'];

    // Only fetch tasks if the employee is actually in a team.
    if (!empty($team_id)) {
        // Get all tasks assigned to this employee's team.
        $sql_tasks = "SELECT taskID, taskName, location1, taskDesc, dueDate, status FROM tasks WHERE teamID = ? ORDER BY dueDate ASC";
        $stmt_tasks = mysqli_prepare($conn, $sql_tasks);
        mysqli_stmt_bind_param($stmt_tasks, "i", $team_id);
        mysqli_stmt_execute($stmt_tasks);
        $result_tasks = mysqli_stmt_get_result($stmt_tasks);

        if ($result_tasks) {
            // --- This is a nested query: For EACH task, get its proofs. ---
            while ($task = mysqli_fetch_assoc($result_tasks)) {
                $proofs = [];
                $sql_proofs = "SELECT fileName, uploadedBy FROM task_proofs WHERE taskID = ?";
                $stmt_proofs = mysqli_prepare($conn, $sql_proofs);
                mysqli_stmt_bind_param($stmt_proofs, "i", $task['taskID']);
                mysqli_stmt_execute($stmt_proofs);
                $result_proofs_inner = mysqli_stmt_get_result($stmt_proofs);
                while($proof_row = mysqli_fetch_assoc($result_proofs_inner)){
                    $proofs[] = $proof_row;
                }
                mysqli_stmt_close($stmt_proofs);
                // Add the array of proofs we just found into the main task array.
                $task['proofs'] = $proofs;
                // Add the complete task (with its proofs) to our list of tasks to display.
                $my_tasks[] = $task;
            }
        }
        mysqli_stmt_close($stmt_tasks);
    }
}
// Close the remaining database connections.
mysqli_stmt_close($stmt);
mysqli_close($conn);

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Tasks</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/employee.css">
  <style>
    .rounded-table { border-collapse: separate; border-spacing: 0; border-radius: 12px; overflow: hidden; }
  </style>
</head>
<body>

  <!-- NAVBAR, SIDEBAR, and LOGOUT MODAL (Standard navigation elements) -->
  <nav class="navbar bg-light">
    <div class="container-fluid d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">☰</button>
        <a class="navbar-brand mb-0" href="#"><img src="../assets/download-removebg-preview.png" height="24"></a>
      </div>
    </div>
  </nav>
  <div class="offcanvas offcanvas-start bg-dark text-white d-flex flex-column" tabindex="-1" id="sidebarMenu" style="width:280px;">
    <div class="offcanvas-header"><h5 class="offcanvas-title">Menu</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button></div>
    <div class="offcanvas-body d-flex flex-column flex-grow-1 p-0">
      <ul class="nav flex-column flex-grow-1">
        <li class="nav-item mb-2"><a class="nav-link text-white" href="employee-dashboard.php">Dashboard</a></li>
        <li class="nav-item mb-2"><a class="nav-link text-white" href="myTask-employee.php">My Task</a></li>
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

  <!-- Main content area -->
  <div class="container mt-4 d-flex">
    <div class="card mx-auto text-center" style="width: 300px; height: 30px;">
      <strong>MY TASK LIST</strong>
    </div>
  </div>

  <!-- This block displays the success/error message if one was set during form processing. -->
  <?php if (!empty($update_message)): ?>
    <div class="container mt-3">
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($update_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
  <?php endif; ?>

  <!-- This container holds the main tasks table. -->
  <div class="container mt-4">
    <table class="table table-bordered rounded-table table-striped align-middle">
      <thead class="table-dark">
        <tr>
          <th style="width: 2%;">No</th>
          <th style="width: 10%;">Task Name</th>
          <th style="width: 10%;">Location</th>
          <th style="width: 23%;">Description</th>
          <th style="width: 15%;">Due Date</th>
          <th style="width: 17;">Status</th>
          <th style="width: 20%;">Action / Proof</th>
        </tr>
      </thead>
      <tbody>
        <!-- Check if any tasks were found for the user's team. -->
        <?php if (empty($my_tasks)): ?>
            <tr><td colspan="7" class="text-center">You have no tasks assigned to your team.</td></tr>
        <?php else: ?>
            <!-- If tasks were found, loop through each one and create a table row. -->
            <?php foreach ($my_tasks as $index => $task): ?>
            <tr>
                <th scope="row"><?php echo $index + 1; ?></th>
                <td><?php echo htmlspecialchars($task['taskName']); ?></td>
                <td><?php echo htmlspecialchars($task['location1']); ?></td>
                <td><?php echo htmlspecialchars($task['taskDesc']); ?></td>
                <td><?php echo htmlspecialchars($task['dueDate']); ?></td>
                <td class="text-center">
                    <!-- This logic changes what is displayed based on the user's role. -->
                    <?php if ($user_role === 'Leader'): ?>
                        <!-- If the user is a Leader, they see a dropdown form to change the task status. -->
                        <form method="POST" action="myTask-employee.php">
                            <input type="hidden" name="taskID" value="<?php echo $task['taskID']; ?>">
                            <div class="input-group"><select name="status" class="form-select form-select-sm"><option value="Not Started" <?php if($task['status'] == 'Not Started') echo 'selected'; ?>>Not Started</option><option value="In Progress" <?php if($task['status'] == 'In Progress') echo 'selected'; ?>>In Progress</option><option value="Completed" <?php if($task['status'] == 'Completed') echo 'selected'; ?>>Completed</option></select><button type="submit" class="btn btn-sm btn-secondary">Set</button></div>
                        </form>
                    <?php else: ?>
                        <!-- If the user is a regular Team Member, they see a static, colored badge. -->
                        <?php $status = htmlspecialchars($task['status']); $badge_class = 'bg-secondary'; if ($status == 'In Progress') $badge_class = 'bg-warning text-dark'; elseif ($status == 'Completed') $badge_class = 'bg-success'; echo "<span class='badge {$badge_class}'>{$status}</span>"; ?>
                    <?php endif; ?>
                </td>
                <td>
                    <!-- This is the small form for uploading a new proof file. -->
                    <form method="POST" action="myTask-employee.php" enctype="multipart/form-data" class="mb-2">
                        <input type="hidden" name="taskID" value="<?php echo $task['taskID']; ?>">
                        <div class="input-group"><input type="file" name="proof_file" class="form-control form-control-sm" accept=".jpg, .jpeg, .png" required><button type="submit" class="btn btn-sm btn-primary">Upload</button></div>
                    </form>
                    
                    <?php 
                        // This PHP logic determines which buttons to show for viewing/deleting proofs.
                        $has_any_proofs = !empty($task['proofs']); // Does this task have any proofs at all?
                        $user_has_proofs = false; // Does the currently logged-in user have any proofs for this task?
                        if ($has_any_proofs) {
                            foreach ($task['proofs'] as $proof) {
                                if ($proof['uploadedBy'] == $employee_id) {
                                    $user_has_proofs = true; // We found a proof uploaded by this user.
                                    break; // We can stop checking now.
                                }
                            }
                        }
                    ?>
                    
                    <!-- Show the "View All Proofs" button if ANY proofs exist for this task. -->
                    <?php if ($has_any_proofs): ?>
                       <button type="button" class="btn btn-sm btn-outline-info w-100" data-bs-toggle="modal" data-bs-target="#proofsModal" data-task-name="<?php echo htmlspecialchars($task['taskName']); ?>" data-proofs='<?php echo json_encode(array_column($task['proofs'], 'fileName')); ?>'>View All Proofs (<?php echo count($task['proofs']); ?>)</button>
                    <?php endif; ?>
                       
                    <!-- Show the "Delete My Proofs" button ONLY if the logged-in user has proofs to delete. -->
                    <?php if ($user_has_proofs): ?>
                       <form method="POST" action="myTask-employee.php" class="d-grid mt-1">
                            <input type="hidden" name="taskID" value="<?php echo $task['taskID']; ?>">
                            <button type="submit" name="delete_my_proofs" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete all of YOUR proofs for this task?');">Delete My Proofs</button>
                       </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- The Modal for viewing proofs in a carousel -->
  <div class="modal fade" id="proofsModal" tabindex="-1" aria-labelledby="proofsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="proofsModalLabel">Task Proofs</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body"><div id="proofsCarousel" class="carousel slide" data-bs-ride="carousel"><div class="carousel-indicators"></div><div class="carousel-inner"></div><button class="carousel-control-prev" type="button" data-bs-target="#proofsCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Previous</span></button><button class="carousel-control-next" type="button" data-bs-target="#proofsCarousel" data-bs-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Next</span></button></div></div>
        </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // This JavaScript for the proofs modal is the same as on the progress-tracker page.
    // It dynamically builds the image carousel when the "View Proofs" button is clicked.
    document.addEventListener('DOMContentLoaded', function () {
    const proofsModal = document.getElementById('proofsModal');
    if (proofsModal) {
        proofsModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const taskName = button.getAttribute('data-task-name');
            const proofs = JSON.parse(button.getAttribute('data-proofs'));
            const modalTitle = proofsModal.querySelector('.modal-title');
            const carouselIndicators = proofsModal.querySelector('.carousel-indicators');
            const carouselInner = proofsModal.querySelector('.carousel-inner');
            modalTitle.textContent = 'Proofs for: ' + taskName;
            carouselIndicators.innerHTML = '';
            carouselInner.innerHTML = '';
            if (proofs.length === 0) {
                carouselInner.innerHTML = '<div class="text-center p-4">No proofs have been submitted for this task.</div>';
                return;
            }
            proofs.forEach((fileName, index) => {
                const indicator = document.createElement('button');
                indicator.type = 'button';
                indicator.dataset.bsTarget = '#proofsCarousel';
                indicator.dataset.bsSlideTo = index;
                if (index === 0) { indicator.classList.add('active'); indicator.setAttribute('aria-current', 'true'); }
                carouselIndicators.appendChild(indicator);
                const item = document.createElement('div');
                item.classList.add('carousel-item');
                if (index === 0) { item.classList.add('active'); }
                const img = document.createElement('img');
                img.src = '../uploads/proofs/' + fileName;
                img.classList.add('d-block', 'w-100');
                img.style.maxHeight = '70vh';
                img.style.objectFit = 'contain';
                item.appendChild(img);
                carouselInner.appendChild(item);
            });
        });
    }
  });
  </script>
</body>
</html>