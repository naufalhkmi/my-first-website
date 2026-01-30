<?php

session_start();


include '../config/database.php';


// First, we create a container (an "array") to hold our counts. We start them all at 0.
$task_counts = [
    'not_started' => 0, 'in_progress' => 0, 'completed' => 0, 'total' => 0
];

// This is the SQL command to get all the counts in one efficient query.
// - `COUNT(*)` counts all tasks to get the total.
// - `COUNT(CASE WHEN ...)` is a clever way to count only the rows that match a specific condition.
//   It's like saying, "Count this row, but only if its status is 'Not Started'."
$sql_counts = "SELECT
            COUNT(CASE WHEN status = 'Not Started' THEN 1 END) AS not_started,
            COUNT(CASE WHEN status = 'In Progress' THEN 1 END) AS in_progress,
            COUNT(CASE WHEN status = 'Completed' THEN 1 END) AS completed,
            COUNT(*) AS total
        FROM tasks";

// Send the SQL query to the database.
$result_counts = mysqli_query($conn, $sql_counts);

// Check if the query was successful and if it returned at least one row of results.
if ($result_counts && $row = mysqli_fetch_assoc($result_counts)) {
    // If we got results, update our `$task_counts` array with the real numbers from the database.
    // We use `(int)` to make sure the values are treated as numbers, not text.
    $task_counts['not_started'] = (int)$row['not_started'];
    $task_counts['in_progress'] = (int)$row['in_progress'];
    $task_counts['completed'] = (int)$row['completed'];
    $task_counts['total'] = (int)$row['total'];
}


// --- 2. Get all Tasks that have a due date to show on the Calendar ---

// Create an empty container (an array) to hold all the events for the calendar.
$calendar_events = [];

// This SQL command selects the necessary details for tasks that can be placed on a calendar.
// The `WHERE dueDate IS NOT NULL` part is important because we can't show a task on the calendar if it has no date.
$sql_events = "SELECT taskName, dueDate, status FROM tasks WHERE dueDate IS NOT NULL";

// Send the query to the database.
$result_events = mysqli_query($conn, $sql_events);

// Check if the query was successful.
if ($result_events) {
    // Since there could be many tasks, we loop through every single row the database returned.
    while ($row = mysqli_fetch_assoc($result_events)) {
        // We want to give each event a color based on its status to make the calendar easy to read.
        $color = '#6c757d'; // A default grey color.
        if ($row['status'] == 'Completed') {
            $color = '#198754'; // Green for completed tasks.
        } elseif ($row['status'] == 'In Progress') {
            $color = '#ffc107'; // Yellow for tasks in progress.
        }
        // For 'Not Started' tasks, it will use the default grey color.

        // The FullCalendar library needs events to be in a specific format (with keys like 'title', 'start', 'color').
        // We create a small package of information for each task and add it to our `$calendar_events` array.
        $calendar_events[] = [
            'title' => $row['taskName'],
            'start' => $row['dueDate'],
            'color' => $color,
            'borderColor' => $color // We set the border color to match for a solid look.
        ];
    }
}

// It's good practice to close the database connection when we are finished getting all our data.
// This frees up resources on the server.
mysqli_close($conn);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- This section loads all the necessary 'style sheets' (CSS) and 'scripts' (JavaScript libraries) -->
  <!-- that our page needs to look good and be interactive. -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/admin.css">
  <!-- JavaScript libraries for the chart and the calendar -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>

  <!-- These are our own custom style rules to make the page look unique. -->
  <style>
    /* This makes the cards lift up slightly when you hover over them with the mouse. */
    .hover-card {
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .hover-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }
    
    /* We set a fixed height for the chart and calendar containers so they look neat. */
    #chart-container { height: 400px; }
    #calendar-container { height: 400px; }

    /* --- These styles make the FullCalendar library look more modern and clean. --- */
    .fc { /* The main calendar container */
      background-color: #fff;
      border-radius: 8px;
      padding: 10px;
    }
    .fc .fc-toolbar-title { /* The "Month Year" title */
      font-size: 1.25rem;
    }
    .fc-daygrid-event { /* The little bubbles for each task event */
      border-radius: 4px;
      padding: 2px 5px;
      font-size: 0.8em;
      color: #fff !important; /* Make sure the event text is white so it's readable on colored backgrounds. */
    }
  </style>
</head>
<body>

  <!-- This is the navigation section, including the top bar and the slide-out sidebar menu. -->
  <!-- It's the same on most admin pages for a consistent user experience. -->
  <nav class="navbar">
    <div class="container-fluid d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center gap-2">
        <!-- The "data-bs-toggle" and "data-bs-target" are special Bootstrap attributes that tell its JavaScript what to do. -->
        <!-- This says: "when this button is clicked, open the offcanvas element with the ID #sidebarMenu". -->
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

  <!-- MAIN CONTENT AREA -->
  <div class="container-fluid mt-4">
    <!-- First Row: The four information cards with the task counts. -->
    <div class="row">
      <!-- In each card, we use PHP's `echo` to print the numbers we got from the database earlier. -->
      <div class="col-lg-3 col-md-6 mb-4">
        <div class="card h-100 hover-card" style="border: 7px solid lightpink; border-radius: 40px;">
          <div class="card-body d-flex flex-column justify-content-center text-center">
            <h5 class="card-title">Not Started</h5>
            <p class="card-text fs-1 fw-bold">
              <?php echo $task_counts['not_started']; ?>
            </p>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 mb-4">
        <div class="card h-100 hover-card" style="border: 7px solid lightyellow; border-radius: 40px;">
          <div class="card-body d-flex flex-column justify-content-center text-center">
            <h5 class="card-title">In Progress</h5>
            <p class="card-text fs-1 fw-bold">
              <?php echo $task_counts['in_progress']; ?>
            </p>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 mb-4">
        <div class="card h-100 hover-card" style="border: 7px solid lightgreen; border-radius: 40px;">
          <div class="card-body d-flex flex-column justify-content-center text-center">
            <h5 class="card-title">Completed</h5>
            <p class="card-text fs-1 fw-bold">
              <?php echo $task_counts['completed']; ?>
            </p>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 mb-4">
        <div class="card h-100 hover-card" style="border: 7px solid lightblue; border-radius: 40px;">
          <div class="card-body d-flex flex-column justify-content-center text-center">
            <h5 class="card-title">All Tasks</h5>
            <p class="card-text fs-1 fw-bold">
              <?php echo $task_counts['total']; ?>
            </p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Second Row: The bar chart and the calendar. -->
    <div class="row">
      <div class="col-lg-8 mb-4">
        <div class="card h-100 hover-card">
          <div class="card-body">
            <h5 class="card-title">Task Status Overview</h5>
            <!-- This is where the bar chart will be drawn by the JavaScript below. -->
            <div id="chart-container"><canvas id="taskBarChart"></canvas></div>
          </div>
        </div>
      </div>
      <div class="col-lg-4 mb-4">
        <div class="card h-100 hover-card">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title">Calendar</h5>
            <!-- This is where the calendar will be drawn by the JavaScript below. -->
            <div id="calendar-container" class="flex-grow-1"><div id='calendar'></div></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // This whole block of JavaScript waits until the entire HTML page has been loaded by the browser before it tries to run.
    // This is important because it can't find elements like 'taskBarChart' or 'calendar' if they don't exist on the page yet.
    document.addEventListener('DOMContentLoaded', function() {
      
      // --- SCRIPT TO CREATE THE BAR CHART ---
      
      // This is a key step! We take our PHP array of task counts and convert it into a JavaScript object using `json_encode`.
      // This is the main way we pass data from the server (PHP) to the user's browser (JavaScript).
      const taskData = <?php echo json_encode($task_counts); ?>;
      
      // We find the 'canvas' element where we want to draw our chart.
      const ctx = document.getElementById('taskBarChart').getContext('2d');
      
      // This creates a new chart using the Chart.js library.
      // We tell it where to draw (`ctx`), what type of chart it is ('bar'), and we give it all the data and styling options.
      const myChart = new Chart(ctx, { 
        type: 'bar',
        data: {
          labels: ['Not Started', 'In Progress', 'Completed', 'All Tasks'],
          datasets: [{
            label: 'Number of Tasks',
            data: [taskData.not_started, taskData.in_progress, taskData.completed, taskData.total],
            backgroundColor: ['rgba(255, 182, 193, 0.6)','rgba(255, 255, 0, 0.6)','rgba(144, 238, 144, 0.6)','rgba(173, 216, 230, 0.6)'],
            borderColor: ['rgba(255, 105, 180, 1)','rgba(204, 204, 0, 1)','rgba(34, 139, 34, 1)','rgba(0, 0, 255, 1)'],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true, maintainAspectRatio: false, // These make the chart fit nicely in its container.
          plugins: { legend: { display: false } }, // We hide the legend since the labels are clear.
          scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } // Make the Y-axis count in whole numbers (1, 2, 3...).
        }
      });

      // --- SCRIPT TO CREATE THE CALENDAR ---
      
      // Find the 'div' element where the calendar should be drawn.
      const calendarEl = document.getElementById('calendar');
      
      // Just like with the chart, we pass our PHP array of events to JavaScript using `json_encode`.
      const calendarEvents = <?php echo json_encode($calendar_events); ?>;

      // This creates a new calendar using the FullCalendar library.
      const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth', // The calendar will start in a standard month view.
        headerToolbar: { // This defines the buttons at the top of the calendar.
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,listWeek' // 'listWeek' is a useful view to see tasks as a list.
        },
        events: calendarEvents, // This is the most important part: we give it our list of tasks to display.
        height: '100%', // This makes the calendar fill the container we put it in.
        
        // This makes the calendar interactive. We're telling it: 'When a user clicks on an event, run this function.'
        eventClick: function(info) {
          info.jsEvent.preventDefault(); // This stops the browser from doing anything else.
          alert('Task: ' + info.event.title); // Show a simple pop-up alert with the task's title.
        }
      });

      // This final command tells the calendar to actually draw itself on the page.
      calendar.render();
    });
  </script>
</body>
</html>