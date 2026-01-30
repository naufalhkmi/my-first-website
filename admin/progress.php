<?php
// Starts or continues a user's session.
session_start();
// Includes the database connection file. The script will stop if this file is not found.
include '../config/database.php';


// --- SECTION 1: DATA PREPARATION & INITIALIZATION ---

// 1. Get all teams from the database to populate the search dropdown menu.
//    This runs every time the page loads, so the dropdown is always available.
$teams_list = [];
$sql_teams = "SELECT teamID, teamName FROM team ORDER BY teamName ASC";
$result_teams = mysqli_query($conn, $sql_teams);
if ($result_teams) {
    // Loop through each team found and add it to our `$teams_list` array.
    while($row = mysqli_fetch_assoc($result_teams)) {
        $teams_list[] = $row;
    }
}

// 2. Initialize variables that we will use later.
//    `$progress_items` will hold the list of tasks for the selected team.
//    `$searched_team_id` will store the ID of the team the user searched for.
$progress_items = [];
$searched_team_id = '';


// --- SECTION 2: HANDLE THE SEARCH ---

// This is the main logic for the page. It checks if the user has selected a team
// from the dropdown and clicked the "Search" button.
// `!empty(...)` is a good check because it makes sure a team was actually selected, not just an empty value.
if (isset($_GET['team_id_search']) && !empty($_GET['team_id_search'])) {
    
    // Get the team ID that the user searched for and store it.
    // We cast it to an integer `(int)` for security.
    $searched_team_id = (int)$_GET['team_id_search'];

    // This is the SQL command to get all tasks for the *specific team* that was searched.
    // - We SELECT all the task details we need, including the `updatedAt` timestamp, which we'll use as the completion date.
    // - We JOIN with the `team` table to get the team's name.
    // - The `WHERE tasks.teamID = ...` clause is the most important part; it filters the results to only this one team.
    // - We sort by due date to see the most urgent tasks first.
    //   SECURITY NOTE: Injecting a variable directly into SQL like `{$searched_team_id}` is risky.
    //   Using prepared statements (like we do for proofs below) is the safest method.
    $sql = "SELECT 
                tasks.taskID,
                tasks.taskName,
                tasks.location1,
                tasks.updatedAt,
                tasks.dueDate,
                tasks.status,
                team.teamName
            FROM tasks
            JOIN team ON tasks.teamID = team.teamID
            WHERE tasks.teamID = {$searched_team_id}
            ORDER BY tasks.dueDate ASC";
            
    // Send the query to the database.
    $result = mysqli_query($conn, $sql);

    // Check if the query was successful and if it found any tasks for that team.
    if ($result && mysqli_num_rows($result) > 0) {
        // --- This is a nested loop: for each task, we do another query to get its proofs ---
        while ($task = mysqli_fetch_assoc($result)) {
            // Create an empty container to hold the image filenames for this specific task's proofs.
            $proofs = [];
            // This is the SQL query to get all proof filenames for the current task's ID.
            // We use a `?` placeholder for security. This is a PREPARED STATEMENT.
            $sql_proofs = "SELECT fileName FROM task_proofs WHERE taskID = ?";
            // Prepare the statement.
            $stmt_proofs = mysqli_prepare($conn, $sql_proofs);
            // Safely bind the current task's ID to the `?` placeholder. 'i' means it's an integer.
            mysqli_stmt_bind_param($stmt_proofs, "i", $task['taskID']);
            // Execute the query.
            mysqli_stmt_execute($stmt_proofs);
            // Get the results.
            $result_proofs = mysqli_stmt_get_result($stmt_proofs);
            // Loop through all the proof files found for this one task.
            while($proof_row = mysqli_fetch_assoc($result_proofs)){
                // Add the filename to our `$proofs` array.
                $proofs[] = $proof_row['fileName'];
            }
            // Close the prepared statement to free up resources.
            mysqli_stmt_close($stmt_proofs);
            
            // Now, we add the list of proofs we just found as a new piece of information inside our main `$task` array.
            $task['proofs'] = $proofs; 
            // Finally, we add the complete task (with its proofs included) to our main `$progress_items` list.
            $progress_items[] = $task;
        }
    }
}

// Close the main database connection as we are done fetching all data.
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Progress Tracker</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/admin.css">
  <style>
     /* This gives the main table nice rounded corners. */
     .rounded-table { border-collapse: separate; border-spacing: 0; border-radius: 12px; overflow: hidden; }
  </style>
</head>

<body>

  <!-- NAVBAR, SIDEBAR, and LOGOUT MODAL -->
  <nav class="navbar bg-light">
    <div class="container-fluid d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
          ☰
        </button>
        <a class="navbar-brand mb-0" href="#"><img src="../assets/download-removebg-preview.png" height="24" alt="Logo"></a>
      </div>
      <a class="d-flex align-items-center gap-2 text-decoration-none text-white" href="#">
        <span>Hi Admin</span>
        <img src="../assets/profile-removebg-preview.png" height="32" alt="Profile">
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

  <!-- MAIN CONTENT -->
  <div class="container mt-4 mb-4">
    <!-- This is the search bar at the top of the page. -->
    <nav class="navbar" style="border-radius: 50px;">
      <div class="container-fluid">
        <!-- The form sends its data back to this same page using the GET method, which puts the data in the URL. -->
        <form method="GET" action="progress.php" class="d-flex w-100" role="search">
          <select class="form-select me-2" name="team_id_search" aria-label="Select Team">
            <option value="">-- Select a Team to Search --</option>
            <!-- Here, we use PHP to loop through the `$teams_list` we fetched earlier -->
            <!-- and create an <option> for each team in the dropdown menu. -->
            <?php foreach($teams_list as $team): ?>
              <!-- The `if` statement checks if the current team in the loop is the one the user already searched for. -->
              <!-- If it is, we add the 'selected' attribute to keep it selected in the dropdown after the page reloads. -->
              <option value="<?php echo $team['teamID']; ?>" <?php if ($team['teamID'] == $searched_team_id) echo 'selected'; ?>><?php echo htmlspecialchars($team['teamName']); ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn btn btn-success" type="submit">Search</button>
        </form>
      </div>
    </nav>
  </div>

  <!-- This container holds the main results table. -->
  <div class="container mt-4">
    <table class="table table-bordered rounded-table table-striped align-middle">
      <thead class="table-dark">
        <tr>
          <!-- These are the headers for our results table. -->
          <th scope="col" style="width: 5%;">No</th>
          <th scope="col" style="width: 10%;">Team</th>
          <th scope="col" style="width: 15%;">Task</th>
          <th scope="col" style="width: 15%;">Location</th>
          <th scope="col" style="width: 15%;">Completed At</th>
          <th scope="col" style="width: 15%;">Due Date</th>
          <th scope="col" style="width: 10%;">Status</th>
          <th scope="col" style="width: 10%;">Proof</th>
        </tr>
      </thead>
      <tbody>
          <!-- This PHP block checks if our `$progress_items` array is empty. -->
          <?php if (empty($progress_items)): ?>
              <tr>
                  <!-- If it's empty, we show a helpful message spanning all 7 columns. -->
                  <td colspan="8" class="text-center">
                      <!-- We show a different message depending on whether the user has searched yet or not. -->
                      <?php if (!empty($searched_team_id)) { echo "No tasks found for the selected team."; } else { echo "Please select a team and click 'Search' to view progress."; } ?>
                  </td>
              </tr>
          <?php else: ?>
              <!-- If there are tasks, we loop through each one and create a table row. -->
              <?php foreach ($progress_items as $index => $item): ?>
              <tr>
                  <th scope="row"><?php echo $index + 1; ?></th>
                  <!-- `htmlspecialchars()` is a security function to prevent hacking (XSS). -->
                  <td><?php echo htmlspecialchars($item['teamName']); ?></td>
                  <td><?php echo htmlspecialchars($item['taskName']); ?></td>
                  <td><?php echo htmlspecialchars($item['location1']); ?></td>
                  <td>
                      <?php 
                          // This block displays the completion date.
                          // It only shows a date IF the task status is 'Completed' AND the timestamp is not an empty default.
                          if ($item['status'] == 'Completed' && $item['updatedAt'] != '0000-00-00 00:00:00') {
                              // `strtotime` converts the database timestamp into a format `date()` can understand.
                              // We format it as Year-Month-Day.
                              echo date('Y-m-d', strtotime($item['updatedAt']));
                          } else {
                              echo 'Not Completed Yet';
                          }
                      ?>
                  </td>
                  <td><?php echo htmlspecialchars($item['dueDate']); ?></td>
                  <td>
                      <?php 
                          // This block creates a colored badge for the status to make it easy to see.
                          $status = htmlspecialchars($item['status']); 
                          $badge_class = 'bg-secondary'; // Default badge color
                          if ($status == 'In Progress') { $badge_class = 'bg-warning text-dark'; } 
                          elseif ($status == 'Completed') { $badge_class = 'bg-success'; } 
                          echo "<span class='badge {$badge_class}'>{$status}</span>"; 
                      ?>
                  </td>
                  <td>
                      <!-- This block creates the "View Proof" button. -->
                      <!-- It first checks if the 'proofs' array for this task has any files in it. -->
                      <?php if (!empty($item['proofs'])): ?>
                          <!-- If there are proofs, it creates a button. -->
                          <!-- `data-bs-toggle` and `data-bs-target` tell Bootstrap to open the modal. -->
                          <!-- The `data-proofs` attribute holds the list of filenames as a JSON string, ready for JavaScript to use. -->
                          <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#proofsModal" data-task-name="<?php echo htmlspecialchars($item['taskName']); ?>" data-proofs='<?php echo json_encode($item['proofs']); ?>'>
                             View (<?php echo count($item['proofs']); ?>)
                         </button>
                      <?php else: ?>
                          <!-- If there are no proofs, it just displays "None". -->
                          None
                      <?php endif; ?>
                  </td>
              </tr>
              <?php endforeach; ?>
          <?php endif; ?>
      </tbody>
    </table>
  </div>
  
  <!-- This is the Modal pop-up window for viewing proof images. It is hidden by default. -->
  <div class="modal fade" id="proofsModal" tabindex="-1" aria-labelledby="proofsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="proofsModalLabel">Task Proofs</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body"><div id="proofsCarousel" class="carousel slide" data-bs-ride="carousel"><div class="carousel-indicators"></div><div class="carousel-inner"></div><button class="carousel-control-prev" type="button" data-bs-target="#proofsCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Previous</span></button><button class="carousel-control-next" type="button" data-bs-target="#proofsCarousel" data-bs-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Next</span></button></div></div>
        </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  // This JavaScript runs only after the entire HTML page has finished loading.
  document.addEventListener('DOMContentLoaded', function () {
    const proofsModal = document.getElementById('proofsModal');
    // We only proceed if the modal element actually exists on the page.
    if (proofsModal) {
        // Listen for the special 'show.bs.modal' event, which Bootstrap fires right before a modal is shown.
        proofsModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // `relatedTarget` is the "View Proof" button that was clicked.
            // Get the task name and the list of proof filenames from the button's `data-*` attributes.
            const taskName = button.getAttribute('data-task-name');
            // `JSON.parse` is very important. It converts the JSON text string of filenames back into a usable JavaScript array.
            const proofs = JSON.parse(button.getAttribute('data-proofs'));

            // Find the elements inside the modal that we need to update.
            const modalTitle = proofsModal.querySelector('.modal-title');
            const carouselIndicators = proofsModal.querySelector('.carousel-indicators'); // The little dots at the bottom.
            const carouselInner = proofsModal.querySelector('.carousel-inner'); // The main container for the images.
            
            // Set the modal's title.
            modalTitle.textContent = 'Proofs for: ' + taskName;
            // Clear out any old images or indicators from a previous click.
            carouselIndicators.innerHTML = '';
            carouselInner.innerHTML = '';

            // Check if there are any proofs to show.
            if (proofs.length === 0) {
                carouselInner.innerHTML = '<div class="text-center p-4">No proofs found.</div>';
                return; // Stop the function here.
            }

            // If there are proofs, loop through each filename in the array.
            proofs.forEach((fileName, index) => {
                // --- Create the indicator dot ---
                const indicator = document.createElement('button');
                indicator.type = 'button';
                indicator.dataset.bsTarget = '#proofsCarousel';
                indicator.dataset.bsSlideTo = index;
                // The first indicator needs the 'active' class.
                if (index === 0) { indicator.classList.add('active'); indicator.setAttribute('aria-current', 'true'); }
                carouselIndicators.appendChild(indicator);

                // --- Create the carousel item (the slide) ---
                const item = document.createElement('div');
                item.classList.add('carousel-item');
                // The first item also needs the 'active' class.
                if (index === 0) { item.classList.add('active'); }

                // --- Create the image element itself ---
                const img = document.createElement('img');
                img.src = '../uploads/proofs/' + fileName; // Build the full path to the image.
                img.classList.add('d-block', 'w-100'); // Add Bootstrap classes for styling.
                img.style.maxHeight = '70vh'; // Limit the image height to prevent it from being too big.
                img.style.objectFit = 'contain'; // Make sure the image scales properly without distortion.
                
                // Put the image inside the item, and the item inside the carousel.
                item.appendChild(img);
                carouselInner.appendChild(item);
            });
        });
    }
  });
  </script>
</body>

</html>